<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Models\ProjectStatusLog;
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

    // Hisobchi DIDOX shartnomani tayyorlab bo'lgach bosadi — loyiha
    // avtomatik "Yangi Toposyomka" navbatiga o'tadi.
    public function markDidoxDone(int $projectId): void
    {
        $user = auth()->user();
        if (!$user?->isHisobchi() && !$user?->isAdmin()) return;

        $project = Project::where('status', 'yangi_loyihalar')->find($projectId);
        if (!$project) return;

        ProjectStatusLog::where('project_id', $project->id)
            ->whereNull('left_at')
            ->update(['left_at' => now()]);

        ProjectStatusLog::create([
            'project_id' => $project->id,
            'status'     => 'yangi_toposyomka',
            'entered_at' => now(),
        ]);

        $project->update(['status' => 'yangi_toposyomka']);

        $this->dispatch('notify', type: 'success', message: "«{$project->owner_name}» — shartnoma tayyor, Toposyomka navbatiga yuborildi");
    }
}
