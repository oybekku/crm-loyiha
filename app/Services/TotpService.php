<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Google Authenticator (TOTP, RFC 6238) — ilovadagi 6 xonali kod.
 *
 * Telegramdan farqli: server hech qayerga so'rov yubormaydi. Ulash paytida
 * telefon va sayt bitta maxfiy kalitni (secret) bilib oladi, keyin ikkalasi
 * har 30 soniyada kodni o'zi hisoblaydi. Shu sabab internet/Telegram
 * uzilishiga bog'liq emas.
 *
 * Favqulodda o'chirish (.env): TOTP_ENABLED=false → login'da so'ralmaydi.
 */
class TotpService
{
    private const PERIOD = 30;
    private const DIGITS = 6;
    private const B32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function globallyEnabled(): bool
    {
        return (bool) config('services.totp.enabled', true);
    }

    public static function isEnabled(User $user): bool
    {
        return $user->totp_enabled_at !== null && filled($user->totp_secret);
    }

    /** Login'da ilova kodi so'raladimi */
    public static function requiredFor(User $user): bool
    {
        return self::globallyEnabled() && self::isEnabled($user);
    }

    public static function generateSecret(): string
    {
        $s = '';
        for ($i = 0; $i < 32; $i++) $s .= self::B32[random_int(0, 31)];
        return $s;
    }

    /** QR kod ichidagi havola — ilova shu orqali "MAKONN (ism)" yozuvini qo'shadi */
    public static function otpauthUrl(User $user, string $secret): string
    {
        $issuer = config('tenants')[request()->getHost()]['label'] ?? 'MAKONN';
        $issuer = $issuer === 'MAKONN' ? 'MAKONN' : 'MAKONN ' . $issuer;
        $label  = rawurlencode($issuer . ':' . $user->email);
        return "otpauth://totp/{$label}?secret={$secret}&issuer=" . rawurlencode($issuer) . '&digits=' . self::DIGITS . '&period=' . self::PERIOD;
    }

    /**
     * Kodni tekshiradi. Telefon soati biroz farq qilsa ham o'tsin deb ±1 qadam
     * (±30 soniya) qabul qilinadi. $userId berilsa — bir kod ikki marta ishlatilmaydi.
     */
    public static function verify(string $secret, string $code, ?int $userId = null): bool
    {
        $code = preg_replace('/\D/', '', $code);
        if (strlen($code) !== self::DIGITS) return false;

        $now = intdiv(time(), self::PERIOD);
        foreach ([0, -1, 1] as $d) {
            $step = $now + $d;
            if (!hash_equals(self::codeAt($secret, $step), $code)) continue;
            if ($userId) {
                $key = "totp_last_step:{$userId}";
                if ((int) Cache::get($key, 0) >= $step) return false; // allaqachon ishlatilgan
                Cache::put($key, $step, now()->addMinutes(5));
            }
            return true;
        }
        return false;
    }

    public static function verifyUser(User $user, string $code): bool
    {
        return self::isEnabled($user) && self::verify($user->totp_secret, $code, $user->id);
    }

    public static function enable(User $user, string $secret): void
    {
        $user->forceFill(['totp_secret' => $secret, 'totp_enabled_at' => now()])->save();
    }

    public static function disable(User $user): void
    {
        $user->forceFill(['totp_secret' => null, 'totp_enabled_at' => null])->save();
    }

    private static function codeAt(string $secret, int $step): string
    {
        $hash   = hash_hmac('sha1', pack('J', $step), self::base32Decode($secret), true);
        $offset = ord($hash[19]) & 0x0f;
        $bin    = ((ord($hash[$offset]) & 0x7f) << 24) | (ord($hash[$offset + 1]) << 16)
                | (ord($hash[$offset + 2]) << 8) | ord($hash[$offset + 3]);
        return str_pad((string) ($bin % 10 ** self::DIGITS), self::DIGITS, '0', STR_PAD_LEFT);
    }

    private static function base32Decode(string $b32): string
    {
        $bits = '';
        foreach (str_split(strtoupper(rtrim($b32, '='))) as $c) {
            $v = strpos(self::B32, $c);
            if ($v === false) continue;
            $bits .= str_pad(decbin($v), 5, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) $out .= chr(bindec($byte));
        }
        return $out;
    }
}
