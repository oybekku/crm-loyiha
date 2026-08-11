<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Services\TelegramOtpService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;

/**
 * Standart Filament login sahifasi + Telegram orqali 2 bosqichli tasdiqlash (2FA):
 *
 *  1-bosqich: email + parol — muvaffaqiyatli bo'lsa va foydalanuvchi
 *     Telegramga ulangan bo'lsa (telegram_chat_id bor), kirish YAKUNLANMAYDI —
 *     shu foydalanuvchining o'ziga (faqat unga) 6 xonali kod yuboriladi va
 *     forma "kod kiriting" bosqichiga o'tadi.
 *  2-bosqich: kod tekshiriladi — to'g'ri bo'lsa, kirish yakunlanadi.
 *
 * Agar foydalanuvchi hali Telegramga ulanmagan bo'lsa — 2FA talab qilinmaydi,
 * u odatdagidek darhol kiradi (Telegramni "Sozlamalar → Telegram bog'lash"
 * orqali keyinroq ulaydi).
 */
class Login extends BaseLogin
{
    // discoverPages() app/Filament/Pages ichidagi hammasini avtomatik ro'yxatga
    // oladi — bu esa alohida "oddiy sahifa" sifatida ham ro'yxatdan o'tib,
    // ->login() orqali berilgan asosiy login route bilan to'qnashishi mumkin edi.
    protected static bool $isDiscovered = false;

    public bool $otpStep = false;

    public ?int $pendingUserId = null;

    public bool $pendingRemember = false;

    public function authenticate(): ?LoginResponse
    {
        if ($this->otpStep) {
            return $this->verifyLoginOtp();
        }

        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();
        $credentials = $this->getCredentialsFromFormData($data);

        // Filament::auth()->attempt() o'rniga validate() ishlatamiz — bu faqat
        // email/parolni tekshiradi, LEKIN sessiyaga kiritib qo'ymaydi (session
        // migratsiya/CSRF token yangilanishini keltirib chiqarmaydi). 2FA kerak
        // bo'lsa, haqiqiy login FAQAT kod tasdiqlangandan keyin (pastda,
        // verifyLoginOtp() ichida) sodir bo'ladi — shu bilan sahifa hali
        // ochilgan paytdagi sessiya/token o'zgarmasdan qoladi va "sahifa
        // muddati tugadi" xatosi chiqmaydi.
        if (! Filament::auth()->validate($credentials)) {
            $this->throwFailureValidationException();
        }

        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            $this->throwFailureValidationException();
        }

        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            $this->throwFailureValidationException();
        }

        if (
            TelegramOtpService::isConfigured() &&
            TelegramOtpService::otpRequired() &&
            TelegramOtpService::isLinked($user)
        ) {
            $sent = TelegramOtpService::sendLoginOtp($user);

            if (! $sent) {
                Notification::make()
                    ->title('Kod yuborilmadi')
                    ->body('Telegram serveriga ulanib bo\'lmadi — internet/hosting tomonida vaqtinchalik uzilish bo\'lishi mumkin. Birozdan keyin qayta urinib ko\'ring.')
                    ->danger()
                    ->send();

                return null;
            }

            $this->pendingUserId = $user->id;
            $this->pendingRemember = (bool) ($data['remember'] ?? false);
            $this->otpStep = true;

            // Filament forma keshi shu so'rov ichida bir marta ($this->form->getState()
            // chaqirilganda) allaqachon eski (email/parol) sxema bilan yasalib bo'lgan —
            // otpStep o'zgargani formani avtomatik qayta qurmaydi. Shu sabab majburan
            // qayta yasaymiz, aks holda "kod" maydoni o'rniga eski forma ko'rinib qolardi.
            $this->cacheForms();
            $this->form->fill();

            Notification::make()
                ->title('Tasdiqlash kodi Telegram\'ga yuborildi')
                ->body('Botdagi xabardan 6 xonali kodni kiriting.')
                ->success()
                ->send();

            return null;
        }

        // 2FA talab qilinmaydi (Telegramga ulanmagan) — validate() login qilmagani
        // uchun bu yerda haqiqiy login shu yerda amalga oshadi.
        Filament::auth()->login($user, (bool) ($data['remember'] ?? false));
        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function verifyLoginOtp(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();
        $user = $this->pendingUserId ? User::find($this->pendingUserId) : null;

        if (! $user || ! TelegramOtpService::verifyOtp($user, (string) ($data['code'] ?? ''), 'login')) {
            throw ValidationException::withMessages([
                'data.code' => 'Kod noto\'g\'ri yoki muddati tugagan.',
            ]);
        }

        Filament::auth()->login($user, $this->pendingRemember);
        session()->regenerate();

        $this->otpStep = false;
        $this->pendingUserId = null;

        return app(LoginResponse::class);
    }

    /**
     * @return array<int|string, \Filament\Forms\Form>
     */
    protected function getForms(): array
    {
        if ($this->otpStep) {
            return [
                'form' => $this->form(
                    $this->makeForm()
                        ->schema([$this->getOtpCodeFormComponent()])
                        ->statePath('data'),
                ),
            ];
        }

        return parent::getForms();
    }

    /**
     * @return array<\Filament\Actions\Action|\Filament\Actions\ActionGroup>
     */
    protected function getFormActions(): array
    {
        if (! $this->otpStep) {
            return parent::getFormActions();
        }

        return [
            $this->getAuthenticateFormAction(),
            \Filament\Actions\Action::make('resendLoginOtp')
                ->label('Kodni qayta yuborish')
                ->link()
                ->action('resendLoginOtp'),
        ];
    }

    public function resendLoginOtp(): void
    {
        $user = $this->pendingUserId ? User::find($this->pendingUserId) : null;

        if (! $user) {
            return;
        }

        if (TelegramOtpService::sendLoginOtp($user)) {
            Notification::make()->title('Kod qayta yuborildi')->success()->send();
        } else {
            Notification::make()
                ->title('Yuborilmadi')
                ->body('Telegram serveriga ulanib bo\'lmadi, birozdan keyin qayta urinib ko\'ring.')
                ->danger()
                ->send();
        }
    }

    protected function getOtpCodeFormComponent(): TextInput
    {
        return TextInput::make('code')
            ->label('Telegramdan kelgan tasdiqlash kodi')
            ->required()
            ->numeric()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1, 'autocomplete' => 'one-time-code', 'maxlength' => 6]);
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return $this->otpStep ? 'Tasdiqlash kodini kiriting' : parent::getHeading();
    }
}
