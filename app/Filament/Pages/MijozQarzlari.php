<?php

namespace App\Filament\Pages;

use App\Traits\HandlesClientDebts;
use Filament\Pages\Page;

/**
 * Mijozlar qarzlari — Yangi bux'dagi tab bilan bir xil jadval, lekin alohida
 * sahifa: menejerlar ham ko'radi (Yangi bux esa faqat admin — u yerda maosh,
 * kassa va boshqa moliyaviy ma'lumotlar bor). Chap paneldan ochiladi.
 */
class MijozQarzlari extends Page
{
    use HandlesClientDebts;

    protected static string  $view  = 'filament.pages.mijoz-qarzlari';
    protected static ?string $title = 'Mijozlar qarzlari';
    protected static ?string $slug  = 'mijozlar-qarzlari';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    // Chap "status rail" panelida ko'rsatiladi (AdminPanelProvider::statusRailHtml())
    protected static bool $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        return static::canManageClientDebts();
    }

    public function getViewData(): array
    {
        return $this->clientDebtsViewData();
    }
}
