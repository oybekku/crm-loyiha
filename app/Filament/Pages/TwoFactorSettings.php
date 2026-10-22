<?php

namespace App\Filament\Pages;

use App\Services\TotpService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Har bir foydalanuvchi o'zi uchun Google Authenticator'ni ulaydi:
 * QR kodni skaner qiladi → ilovadagi kodni yozib tasdiqlaydi → yoqiladi.
 * Shundan keyin login'da ilovadagi kod so'raladi.
 */
class TwoFactorSettings extends Page
{
    protected static string  $view            = 'filament.pages.two-factor-settings';
    protected static ?string $navigationIcon  = 'heroicon-o-device-phone-mobile';
    protected static ?string $navigationLabel = 'Ikki bosqichli kirish';
    protected static ?string $navigationGroup = 'Sozlamalar';
    protected static ?string $title           = 'Google Authenticator';
    protected static ?string $slug            = 'ikki-bosqichli-kirish';

    public string $code = '';

    public static function canAccess(): bool
    {
        return (bool) auth()->user();
    }

    // Sinov bosqichi — menyuda ko'rinmaydi, faqat to'g'ridan-to'g'ri havola orqali
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    // Ulash paytidagi vaqtinchalik kalit sessiyada turadi — tasdiqlanmaguncha bazaga yozilmaydi
    private function pendingSecret(): string
    {
        if (!session()->has('totp_pending_secret')) {
            session()->put('totp_pending_secret', TotpService::generateSecret());
        }
        return session('totp_pending_secret');
    }

    public function enable(): void
    {
        $user = auth()->user();
        if (TotpService::isEnabled($user)) return;

        $secret = $this->pendingSecret();
        if (!TotpService::verify($secret, $this->code)) {
            $this->addError('code', "Kod noto'g'ri. Ilovadagi hozirgi 6 xonali kodni yozing (telefon soati to'g'ri ekanini tekshiring).");
            return;
        }

        TotpService::enable($user, $secret);
        session()->forget('totp_pending_secret');
        $this->code = '';
        Notification::make()->title('Google Authenticator yoqildi')->body("Endi kirishda ilovadagi kod so'raladi.")->success()->send();
    }

    public function disable(): void
    {
        $user = auth()->user();
        if (!TotpService::verifyUser($user, $this->code)) {
            $this->addError('code', "Kod noto'g'ri — o'chirish uchun ilovadagi hozirgi kodni yozing.");
            return;
        }

        TotpService::disable($user);
        $this->code = '';
        Notification::make()->title("Google Authenticator o'chirildi")->warning()->send();
    }

    public function getViewData(): array
    {
        $user    = auth()->user();
        $enabled = TotpService::isEnabled($user);
        $secret  = $enabled ? null : $this->pendingSecret();

        return [
            'enabled'   => $enabled,
            'enabledAt' => $user->totp_enabled_at,
            'secret'    => $secret ? trim(chunk_split($secret, 4, ' ')) : null,
            'otpauth'   => $secret ? TotpService::otpauthUrl($user, $secret) : null,
        ];
    }
}
