<?php

namespace App\Services;

use App\Models\User;

/**
 * Muhim amallarni (to'lovni tahrirlash/o'chirish, xizmat narxini o'zgartirish,
 * loyihani butunlay o'chirish, admin akkaunti va bosqichlarni tahrirlash)
 * Google Authenticator kodi bilan tasdiqlash.
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
        return self::canConfirm($user) && TotpService::verifyUser($user, (string) $code);
    }

    public static function setupUrl(): string
    {
        return route('filament.admin.pages.ikki-bosqichli-kirish');
    }
}
