<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use App\Models\PaymentLog;
use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

/**
 * "Kim, qachon, nima qildi" — ActivityLog (loyiha o'chirilishi/tahrirlanishi)
 * va PaymentLog (to'lov qo'shilishi/o'zgartirilishi/o'chirilishi) yozuvlarini
 * bitta xronologik jadvalda birlashtirib ko'rsatadi. Faqat admin ko'radi —
 * menejerlar endi o'z akkountida ishlagani uchun hisobdorlik uchun kerak.
 */
class FaoliyatJurnali extends Page
{
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
        $this->dateFrom     = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo       = now()->format('Y-m-d');
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
                'description'    => $log->description,
                'type'           => 'project',
                'danger'         => $log->action === 'project_deleted',
                'linkable'       => $log->action !== 'project_deleted',
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
                'description'    => $log->description,
                'type'           => 'payment',
                'danger'         => $log->action === 'deleted',
                'linkable'       => true,
            ]);

        $all = $activity->concat($payments)->sortByDesc('created_at')->values();

        if ($this->filterType) {
            $all = $all->where('type', $this->filterType)->values();
        }

        return $all;
    }
}
