<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Muhim amallarni (o'chirish, narx o'zgartirish va h.k.) tasdiqlash uchun
 * Telegram orqali bir martalik kod (OTP) yuborish va tekshirish.
 *
 * Ishlash tartibi:
 *  1. Admin "Sozlamalar → Profil"da Telegram botga ulanadi (bir marta).
 *  2. Har bir tasdiqlash so'ralganda, shu adminning Telegram chatiga
 *     tasodifiy 6 xonali kod yuboriladi (5 daqiqa amal qiladi).
 *  3. Admin kodni saytga kiritadi, tizim cache'dagi kod bilan solishtiradi.
 */
class TelegramOtpService
{
    private static function token(): ?string
    {
        return config('services.telegram.bot_token');
    }

    public static function isConfigured(): bool
    {
        return filled(self::token());
    }

    /** false bo'lsa — barcha PIN-tasdiqlashlar bypass qilinadi (masalan Telegramга ulanmayotganda). */
    public static function otpRequired(): bool
    {
        return (bool) config('services.telegram.otp_required', true);
    }

    /** Bog'lash uchun havola — admin shu havolani Telegram'da ochib, botga /start bosadi. */
    public static function linkUrl(User $user): ?string
    {
        $botUsername = config('services.telegram.bot_username');
        if (!$botUsername) return null;

        if (!$user->telegram_link_token) {
            $user->telegram_link_token = Str::random(32);
            $user->save();
        }

        return "https://t.me/{$botUsername}?start={$user->telegram_link_token}";
    }

    public static function isLinked(User $user): bool
    {
        return filled($user->telegram_chat_id);
    }

    public static function unlink(User $user): void
    {
        $user->update(['telegram_chat_id' => null, 'telegram_link_token' => null]);
    }

    /**
     * Webhookdan keladigan /start <token> ni shu tokenga tegishli
     * foydalanuvchiga bog'laydi (chat_id'ni saqlaydi).
     */
    public static function linkByToken(string $token, string $chatId): ?User
    {
        $user = User::where('telegram_link_token', $token)->first();
        if (!$user) return null;

        $user->update(['telegram_chat_id' => $chatId]);
        return $user;
    }

    /**
     * Kod generatsiya qilib, Telegramga ulangan BARCHA foydalanuvchilarga birdaniga
     * yuboradi (faqat so'rovchiga emas) — xabarda kim va nima uchun so'raganini ham
     * yozadi, shu bilan boshqalar nazorat qila oladi (kim tasdiqlasa ham, hammaga
     * "kim, nima qildi" ma'lum bo'ladi). Kodning o'zi esa faqat SO'RAGAN odamning
     * (bu funksiyaga berilgan $user) nomiga bog'lab saqlanadi — tasdiqlash o'sha
     * so'rov oynasida amalga oshiriladi.
     *
     * 60 soniya ichida takroriy chaqirilsa qayta yubormaydi (masalan forma bir necha
     * marta qayta chizilganda ortiqcha xabar ketmasligi uchun) — avvalgi kod amal qiladi.
     */
    public static function sendOtp(User $user, string $purpose = 'tasdiqlash', ?string $actionLabel = null): bool
    {
        if (!self::otpRequired()) {
            return true;
        }
        if (!self::isConfigured() || !self::isLinked($user)) {
            return false;
        }

        $cooldownKey = self::cacheKey($user->id, $purpose) . ':cooldown';
        if (Cache::has($cooldownKey)) {
            return true;
        }

        $code = (string) random_int(100000, 999999);
        Cache::put(self::cacheKey($user->id, $purpose), $code, now()->addMinutes(5));

        $what = $actionLabel ? " — <i>{$actionLabel}</i>" : '';
        $text = "🔐 <b>{$user->name}</b>{$what} uchun tasdiqlash kodi so'radi:\n\n<b>{$code}</b>\n\nBu kod 5 daqiqa amal qiladi. Agar bu so'rovni siz yubormagan bo'lsangiz, e'tiborsiz qoldiring.";

        $recipients = User::whereNotNull('telegram_chat_id')->pluck('telegram_chat_id');

        $ok = false;
        foreach ($recipients as $chatId) {
            $ok = self::send($chatId, $text) || $ok;
        }

        // Cooldown faqat MUVAFFAQIYATLI yuborishdan keyin o'rnatiladi — aks holda
        // tarmoq xatosida "qayta yuborish" 60 soniya bloklanib qolardi.
        if ($ok) {
            Cache::put($cooldownKey, true, now()->addSeconds(60));
        }

        return $ok;
    }

    /**
     * Login paytidagi 2FA kodi — sendOtp() dan farqli, faqat SHU foydalanuvchining
     * o'z Telegramiga yuboriladi (boshqa ulangan xodimlarga emas, chunki bu shaxsiy
     * login kodi, umumiy tasdiqlash emas).
     */
    public static function sendLoginOtp(User $user): bool
    {
        if (!self::isConfigured() || !self::isLinked($user)) {
            return false;
        }

        $cooldownKey = self::cacheKey($user->id, 'login') . ':cooldown';
        if (Cache::has($cooldownKey)) {
            return true;
        }

        $code = (string) random_int(100000, 999999);
        Cache::put(self::cacheKey($user->id, 'login'), $code, now()->addMinutes(5));

        $text = "🔐 Tizimga kirish uchun tasdiqlash kodi:\n\n<b>{$code}</b>\n\nBu kod 5 daqiqa amal qiladi. Agar bu urinish siz tomoningizdan bo'lmasa, e'tiborsiz qoldiring va parolingizni almashtiring.";

        $ok = self::send($user->telegram_chat_id, $text);

        // Cooldown faqat MUVAFFAQIYATLI yuborishdan keyin o'rnatiladi — aks holda
        // tarmoq xatosida foydalanuvchi "qayta yuborish"ni 60 soniya bosolmay qolardi.
        if ($ok) {
            Cache::put($cooldownKey, true, now()->addSeconds(60));
        }

        return $ok;
    }

    public static function verifyOtp(User $user, string $code, string $purpose = 'tasdiqlash'): bool
    {
        if (!self::otpRequired()) {
            return true;
        }

        $key = self::cacheKey($user->id, $purpose);
        $expected = Cache::get($key);
        if (!$expected || !hash_equals((string) $expected, trim($code))) {
            return false;
        }

        Cache::forget($key); // bir martalik — ishlatilgach o'chiriladi
        return true;
    }

    private static function cacheKey(int $userId, string $purpose): string
    {
        return "telegram_otp:{$userId}:{$purpose}";
    }

    /**
     * Beget'dagi Telegram'ga chiquvchi ulanish beqaror (taxminan har 5
     * urinishdan 1 tasi timeout bo'ladi) — shu sabab bir necha marta
     * qayta urinamiz, aks holda foydalanuvchiga tasodifan kod yetib bormaydi.
     */
    private static function send(string $chatId, string $text): bool
    {
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $resp = Http::timeout(6)->post(
                    "https://api.telegram.org/bot" . self::token() . "/sendMessage",
                    ['chat_id' => $chatId, 'text' => $text, 'parse_mode' => 'HTML']
                );
                if ($resp->successful()) {
                    return true;
                }
                Log::warning("TelegramOtpService: yuborish xatosi (urinish {$attempt}/3) — HTTP " . $resp->status());
            } catch (\Throwable $e) {
                Log::warning("TelegramOtpService: yuborish xatosi (urinish {$attempt}/3) — " . $e->getMessage());
            }
        }

        return false;
    }
}
