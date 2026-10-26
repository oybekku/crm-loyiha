<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use App\Models\PaymentLog;
use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\WithPagination;

/**
 * "Kim, qachon, nima qildi" — ActivityLog (loyiha o'chirilishi/tahrirlanishi)
 * va PaymentLog (to'lov qo'shilishi/o'zgartirilishi/o'chirilishi) yozuvlarini
 * bitta xronologik jadvalda birlashtirib ko'rsatadi. Faqat admin ko'radi —
 * menejerlar endi o'z akkountida ishlagani uchun hisobdorlik uchun kerak.
 */
class FaoliyatJurnali extends Page
{
    use WithPagination;

    public const PER_PAGE = 50;

    protected static string  $view            = 'filament.pages.faoliyat-jurnali';
    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Faoliyat jurnali';
    protected static ?string $navigationGroup = 'Sozlamalar';
    protected static ?int    $navigationSort  = 14;
    protected static ?string $title           = 'Faoliyat jurnali';
    protected ?string $heading    = '';
    protected ?string $subheading = '';

    public ?int $filterUserId = null;

    public string $filterType = ''; // '' | 'project' | 'payment'

    public string $dateFrom = '';

    public string $dateTo = '';

    /** Qidiruv: xodim, amal, loyiha raqami, mijoz, tavsif bo'yicha */
    public string $search = '';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function resetFilters(): void
    {
        $this->filterUserId = null;
        $this->filterType   = '';
        $this->search       = '';
        $this->dateFrom     = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo       = now()->format('Y-m-d');
        $this->resetPage();
    }

    // Filtr yoki qidiruv o'zgarsa — 1-sahifaga qaytamiz
    public function updated(string $property): void
    {
        if (in_array($property, ['filterUserId', 'filterType', 'dateFrom', 'dateTo', 'search'], true)) {
            $this->resetPage();
        }
    }

    public function getPageEntriesProperty(): LengthAwarePaginator
    {
        $all  = $this->entries;
        $page = max(1, (int) $this->getPage());

        return new LengthAwarePaginator($all->forPage($page, self::PER_PAGE)->values(), $all->count(), self::PER_PAGE, $page);
    }

    public function getUsersProperty(): Collection
    {
        return User::orderBy('name')->get(['id', 'name']);
    }

    public function getEntriesProperty(): Collection
    {
        $from = $this->dateFrom ? Carbon::parse($this->dateFrom)->startOfDay() : null;
        $to   = $this->dateTo ? Carbon::parse($this->dateTo)->endOfDay() : null;

        $activity = ActivityLog::query()
            ->with(['user', 'project'])
            ->when($this->filterUserId, fn ($q) => $q->where('user_id', $this->filterUserId))
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->get()
            ->map(fn (ActivityLog $log) => [
                'created_at'     => $log->created_at,
                'user'           => $log->user?->name ?? "O'chirilgan foydalanuvchi",
                'action'         => $log->actionLabel(),
                'project_number' => $log->project_number,
                'project_id'     => $log->project_id,
                'owner_name'     => $log->project?->owner_name,
                'description'    => $log->description,
                'type'           => 'project',
                'danger'         => in_array($log->action, ['project_deleted', 'project_force_deleted'], true),
                'linkable'       => !in_array($log->action, ['project_deleted', 'project_force_deleted'], true),
            ]);

        $payments = PaymentLog::query()
            ->with(['user', 'project'])
            ->when($this->filterUserId, fn ($q) => $q->where('user_id', $this->filterUserId))
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->get()
            ->map(fn (PaymentLog $log) => [
                'created_at'     => $log->created_at,
                'user'           => $log->user?->name ?? "O'chirilgan foydalanuvchi",
                'action'         => $log->actionLabel(),
                'project_number' => $log->project?->number,
                'project_id'     => $log->project_id,
                'owner_name'     => $log->project?->owner_name,
                'description'    => $log->description,
                'type'           => 'payment',
                'danger'         => $log->action === 'deleted',
                'linkable'       => true,
            ]);

        $all = $activity->concat($payments)->sortByDesc('created_at')->values();

        if ($this->filterType) {
            $all = $all->where('type', $this->filterType)->values();
        }

        // Qidiruv — har bir so'z alohida (masalan "Jamshid narx" — ikkalasi ham bo'lishi kerak)
        $words = preg_split('/\s+/u', mb_strtolower(trim($this->search)), -1, PREG_SPLIT_NO_EMPTY);
        if ($words) {
            $all = $all->filter(function ($e) use ($words) {
                $hay = mb_strtolower(implode(' ', [$e['user'], $e['action'], $e['project_number'], $e['owner_name'], $e['description']]));
                $hayNoSpace = str_replace(' ', '', $hay); // "1000000" ham "1 000 000"ni topsin
                foreach ($words as $w) {
                    if (!str_contains($hay, $w) && !str_contains($hayNoSpace, $w)) return false;
                }
                return true;
            })->values();
        }

        return $all;
    }
}
