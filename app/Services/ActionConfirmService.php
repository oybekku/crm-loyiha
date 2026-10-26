<?php

namespace App\Services;

use App\Models\User;

/**
 * Muhim amallarni (to'lovni tahrirlash/o'chirish, loyiha narxiga ta'sir
 * qiluvchi har qanday o'zgarish — PriceChangeRecorder::guard(), loyihani
 * butunlay o'chirish, admin akkaunti va bosqichlarni tahrirlash)
 * Google Authenticator kodi bilan tasdiqlash. To'lov kiritish — kodsiz.
 *
 * Ilk bor Telegram kodi bilan edi — Beget'dan Telegramga ulanish beqaror
 * bo'lgani uchun ilovadagi kodga o'tildi: server hech qayerga so'rov
 * yubormaydi. Ilovani ulamagan foydalanuvchi bu amallarni bajara olmaydi
 * (avval "Ikki bosqichli kirish" sahifasida ulashi kerak).
 *
 * Kim nima qilgani "Faoliyat jurnali"da yoziladi (PaymentLog / ActivityLog).
 *
 * Favqulodda o'chirish (.env): ACTION_CONFIRM_REQUIRED=false → kod so'ralmaydi.
 */
class ActionConfirmService
{
    /** Shu so'rovda kod to'g'ri kiritildimi — PriceChangeRecorder::guard() shunga qaraydi */
    private static bool $confirmed = false;

    public static function confirmed(): bool
    {
        return !self::required() || self::$confirmed;
    }

    public static function required(): bool
    {
        return (bool) config('services.totp.action_confirm', true) && TotpService::globallyEnabled();
    }

    /** Foydalanuvchida ilova ulanganmi (ulanmagan bo'lsa — amal bloklanadi) */
    public static function canConfirm(?User $user): bool
    {
        return $user !== null && TotpService::isEnabled($user);
    }

    public static function verify(?User $user, ?string $code): bool
    {
        if (!self::required()) return true;
        $ok = self::canConfirm($user) && TotpService::verifyUser($user, (string) $code);
        if ($ok) self::$confirmed = true;
        return $ok;
    }

    public static function setupUrl(): string
    {
        return route('filament.admin.pages.ikki-bosqichli-kirish');
    }
}
