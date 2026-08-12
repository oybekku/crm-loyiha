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

    // yangi = yangi ochilgan loyihalar (DIDOX shartnoma tuzish),
    // yangi_didox = admin/menejer "O'tkazish → Yangi Didox" orqali TANLAB
    //   yuborgan loyihalar (barcha "Yangi loyihalar" emas, faqat DIDOX
    //   kerak bo'lganlari),
    // tugallangan = admin "O'tkazish → DIDOX" orqali yuborgan tugagan loyihalar
    public string $tab = 'yangi';

    protected const TABS = ['yangi', 'yangi_didox', 'tugallangan'];

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, self::TABS, true) ? $tab : 'yangi';
    }

    protected function getViewData(): array
    {
        $status = match ($this->tab) {
            'yangi_didox'  => 'yangi_didox',
            'tugallangan'  => 'didox',
            default        => 'yangi_loyihalar',
        };
        $projects = Project::where('status', $status)
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

    // Hisobchi "Tugallangan loyihalar" (DIDOX) navbatida shot-fakturani
    // jo'natib bo'lgach bosadi — loyiha "Tugallangan"ga qaytadi. Qachon
    // bajarilgani ProjectStatusLog'dagi 'didox' yozuvining left_at'ida qoladi.
    public function markInvoiceDone(int $projectId): void
    {
        $user = auth()->user();
        if (!$user?->isHisobchi() && !$user?->isAdmin()) return;

        $project = Project::where('status', 'didox')->find($projectId);
        if (!$project) return;

        ProjectStatusLog::where('project_id', $project->id)
            ->whereNull('left_at')
            ->update(['left_at' => now()]);

        ProjectStatusLog::create([
            'project_id' => $project->id,
            'status'     => 'tugallangan',
            'entered_at' => now(),
        ]);

        $project->update(['status' => 'tugallangan', 'invoice_sent_at' => now()]);

        $this->dispatch('notify', type: 'success', message: "«{$project->owner_name}» — shot-faktura yuborildi, Tugallangan bo'limiga qaytdi");
    }
}
