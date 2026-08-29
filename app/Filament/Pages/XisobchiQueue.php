<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Models\ProjectStatusLog;
use Filament\Pages\Page;

// Faqat "hisobchi" (va nazorat uchun admin) ko'radigan, ruxsatnomalar
// tizimidan mustaqil sahifa — admin/menejer "Yangi Didox"ga TANLAB
// o'tkazgan loyihalarni (shartnoma tuzish) va shot-faktura kutayotgan
// tugallangan loyihalarni ko'rsatadi. Boshqa hech qanday CRM ma'lumoti
// (moliya, boshqa loyihalar, xodimlar) shu sahifada ko'rinmaydi.
class XisobchiQueue extends Page
{
    protected static string  $view            = 'filament.pages.xisobchi-queue';
    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Yangi loyihalar (DIDOX)';
    protected static ?string $navigationGroup = 'Loyihalar';
    protected static ?string $title           = 'Yangi Didox — shartnoma tuzish';
    protected static ?int    $navigationSort  = 1;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->isHisobchi() || $user?->isAdmin();
    }

    // yangi_didox = admin/menejer "O'tkazish → Yangi Didox" orqali TANLAB
    //   yuborgan loyihalar (DIDOX shartnoma tuzish kerak bo'lganlari),
    // tugallangan = is_didox=true VA holati "tugallangan" bo'lgan, lekin
    //   hali shot-faktura yuborilmagan loyihalar — AVTOMATIK chiqadi,
    //   admin/menejer qo'lda status o'zgartirishi shart emas (loyiha
    //   qachon "Yangi Didox"dan o'tgan bo'lsa, doimiy is_didox belgisi
    //   tufayli — ish qachon va qanday tugatilishidan qat'i nazar shu
    //   yerda ko'rinadi).
    public string $tab = 'yangi_didox';

    protected const TABS = ['yangi_didox', 'tugallangan'];

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, self::TABS, true) ? $tab : 'yangi_didox';
    }

    protected function getViewData(): array
    {
        $projects = match ($this->tab) {
            'tugallangan' => Project::where('is_didox', true)
                ->where('status', 'tugallangan')
                ->whereNull('invoice_sent_at')
                ->orderBy('created_at')
                ->get(),
            default => Project::where('status', 'yangi_didox')->orderBy('created_at')->get(),
        };

        return compact('projects');
    }

    // Hisobchi DIDOX shartnomani tayyorlab bo'lgach bosadi — loyiha
    // avtomatik "Yangi Toposyomka" navbatiga o'tadi.
    public function markDidoxDone(int $projectId): void
    {
        $user = auth()->user();
        if (!$user?->isHisobchi() && !$user?->isAdmin()) return;

        $project = Project::where('status', 'yangi_didox')->find($projectId);
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
    // jo'natib bo'lgach bosadi. Loyiha status'i o'zgarmaydi (allaqachon
    // "tugallangan") — faqat invoice_sent_at belgilanadi, shu bilan bu
    // ro'yxatdan avtomatik chiqib ketadi.
    public function markInvoiceDone(int $projectId): void
    {
        $user = auth()->user();
        if (!$user?->isHisobchi() && !$user?->isAdmin()) return;

        $project = Project::where('is_didox', true)
            ->where('status', 'tugallangan')
            ->whereNull('invoice_sent_at')
            ->find($projectId);
        if (!$project) return;

        $project->update(['invoice_sent_at' => now()]);

        $this->dispatch('notify', type: 'success', message: "«{$project->owner_name}» — shot-faktura yuborildi");
    }
}
