<?php

namespace App\Filament\Pages;

use App\Models\Project;
use Filament\Pages\Page;

// Faqat "hisobchi" (va nazorat uchun admin) ko'radigan, ruxsatnomalar
// tizimidan mustaqil sahifa — "Yangi loyihalar" bosqichiga tushgan
// loyihalarni ko'rsatadi (DIDOX shartnoma tuzish uchun). Boshqa hech qanday
// CRM ma'lumoti (moliya, boshqa loyihalar, xodimlar) shu sahifada ko'rinmaydi.
class XisobchiQueue extends Page
{
    protected static string  $view            = 'filament.pages.xisobchi-queue';
    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Yangi loyihalar (DIDOX)';
    protected static ?string $title           = 'Yangi loyihalar — shartnoma tuzish';
    protected static ?int    $navigationSort  = 1;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->isHisobchi() || $user?->isAdmin();
    }

    protected function getViewData(): array
    {
        $projects = Project::where('status', 'yangi_loyihalar')
            ->orderBy('created_at')
            ->get();

        return compact('projects');
    }
}
