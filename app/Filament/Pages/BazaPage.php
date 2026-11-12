<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Services\ActionConfirmService;
use App\Services\BazaService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\WithPagination;

/**
 * BAZA — eskirgan loyihalar (faqat admin, faqat o'qish uchun).
 * Bu yerdagi loyihalar asosiy saytning hech qayerida ko'rinmaydi (HideBazaScope).
 * "↩ Asosiyga qaytarish" — hammasini ko'chirishdan oldingi holatiga qaytaradi.
 */
class BazaPage extends Page
{
    use WithPagination;

    protected static string  $view            = 'filament.pages.baza';
    protected static ?string $navigationIcon  = 'heroicon-o-circle-stack';
    protected static ?string $navigationLabel = 'Baza';
    protected static ?string $navigationGroup = 'Loyihalar';
    protected static ?int    $navigationSort  = 4;
    protected static ?string $slug            = 'baza';
    protected static ?string $title           = '';

    public string $search     = '';
    public ?int   $selectedId = null;

    // Qaytarish (Google Authenticator kodi bilan)
    public ?int   $restoreId    = null;
    public string $restoreCode  = '';
    public bool   $restoreError = false;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    // Shu sahifada — haqiqiy ism/manzil ko'rinadi (boshqa joyda "📦 BAZA LOYIHASI")
    public function boot(): void
    {
        Project::$revealBaza = true;
    }

    public function updatedSearch(): void { $this->resetPage(); }

    public function selectProject(?int $id): void
    {
        $this->selectedId = ($this->selectedId === $id) ? null : $id;
    }

    public function askRestore(int $id): void
    {
        $this->restoreId    = $id;
        $this->restoreCode  = '';
        $this->restoreError = false;
    }

    public function cancelRestore(): void
    {
        $this->reset(['restoreId', 'restoreCode', 'restoreError']);
    }

    public function confirmRestore(): void
    {
        $user = auth()->user();
        if (!$user?->isAdmin() || !$this->restoreId) return;

        if (!ActionConfirmService::verify($user, $this->restoreCode)) {
            $this->restoreError = true;
            return;
        }

        $ok = BazaService::restore($this->restoreId, $user);
        if ($this->selectedId === $this->restoreId) $this->selectedId = null;
        $this->cancelRestore();

        $ok
            ? Notification::make()->title("↩ Loyiha asosiy saytga qaytarildi")->success()->send()
            : Notification::make()->title("Loyiha topilmadi")->warning()->send();
    }

    public function getViewData(): array
    {
        $base = Project::onlyBaza();

        $q = (clone $base)->with(['services', 'payments'])->orderByDesc('baza_at');
        if (trim($this->search) !== '') {
            $s   = trim($this->search);
            $num = ltrim($s, '№#');
            $q->where(fn ($x) => $x->where('owner_name', 'like', "%{$s}%")
                ->orWhere('address', 'like', "%{$s}%")
                ->orWhere('phones', 'like', "%{$s}%")
                ->orWhere('number', 'like', "%{$s}%")
                ->orWhere('seq_no', ctype_digit($num) ? (int) $num : -1));
        }

        $stats = (clone $base)->selectRaw('COUNT(*) c, COALESCE(SUM(paid_amount),0) paid, COALESCE(SUM(GREATEST(total_price - paid_amount, 0)),0) debt')->first();

        $selected = $this->selectedId
            ? Project::onlyBaza()->with(['services.assignedUser', 'payments.account', 'payments.createdBy', 'files', 'statusLogs'])->find($this->selectedId)
            : null;

        $movers = \App\Models\User::whereIn('id', (clone $base)->whereNotNull('baza_by')->distinct()->pluck('baza_by'))->pluck('name', 'id');

        return [
            'projects'      => $q->paginate(25),
            'stats'         => $stats,
            'selected'      => $selected,
            'movers'        => $movers,
            'statusLabels'  => \App\Models\ProjectStatus::pluck('label', 'key')->toArray() + Project::statusOptions(),
            'methodOptions' => \App\Models\Payment::methodOptions(),
        ];
    }
}
