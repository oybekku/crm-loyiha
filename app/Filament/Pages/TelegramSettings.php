<?php

namespace App\Filament\Pages;

use App\Services\TelegramOtpService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Har bir foydalanuvchi shu sahifada o'z Telegramini bog'laydi — shundan
 * keyin muhim amallarni (o'chirish, narx o'zgartirish) tasdiqlash kodi
 * qattiq yozilgan PIN o'rniga shu yerga keladi.
 */
class TelegramSettings extends Page
{
    protected static string  $view            = 'filament.pages.telegram-settings';
    protected static ?string $navigationIcon  = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Telegram bog\'lash';
    protected static ?string $navigationGroup = 'Sozlamalar';
    protected static ?int    $navigationSort  = 13;
    protected static ?string $title           = 'Telegram orqali tasdiqlash';

    public static function canAccess(): bool
    {
        return (bool) auth()->user();
    }

    // Xavfsizlik uchun chap menyuda ko'rinmaydi — faqat to'g'ridan-to'g'ri
    // havola orqali ochiladi (admin kimgadir ulash uchun beradi).
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function unlink(): void
    {
        TelegramOtpService::unlink(auth()->user());
        Notification::make()->title("Telegram bog'lanishi bekor qilindi")->warning()->send();
    }

    public function getViewData(): array
    {
        $user = auth()->user();

        return [
            'configured' => TelegramOtpService::isConfigured(),
            'linked'     => TelegramOtpService::isLinked($user),
            'linkUrl'    => TelegramOtpService::linkUrl($user),
        ];
    }
}
