<?php

namespace App\Filament\Pages;

use App\Models\PriceChange;
use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

/**
 * "Narx o'zgarishlari" — kim, qachon, qaysi loyiha narxini qanchadan
 * qanchaga o'zgartirdi (PriceChangeRecorder yozadi). Faqat admin ko'radi.
 * Yangi o'zgarishda adminlarga panelda 🔔 xabar ham keladi.
 */
class NarxOzgarishlari extends Page
{
    protected static string  $view            = 'filament.pages.narx-ozgarishlari';
    protected static ?string $navigationIcon  = 'heroicon-o-arrows-up-down';
    protected static ?string $navigationLabel = "Narx o'zgarishlari";
    protected static ?string $navigationGroup = 'Sozlamalar';
    protected static ?int    $navigationSort  = 13;
    protected static ?string $title           = "Narx o'zgarishlari";
    protected static ?string $slug            = 'narx-ozgarishlari';
    protected ?string $heading    = '';
    protected ?string $subheading = '';

    public ?int $filterUserId = null;

    public string $filterKind = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    /** Menyuda: oxirgi 7 kundagi o'zgarishlar soni */
    public static function getNavigationBadge(): ?string
    {
        $n = PriceChange::where('created_at', '>=', now()->subDays(7))->count();
        return $n ? (string) $n : null;
    }

    public function mount(): void
    {
        $this->resetFilters();
        // Sahifaga kirildi — narx xabarlari o'qilgan bo'ladi
        auth()->user()->unreadNotifications()->where('data', 'like', '%narx-ozgarishlari%')->update(['read_at' => now()]);
    }

    public function resetFilters(): void
    {
        $this->filterUserId = null;
        $this->filterKind   = '';
        $this->dateFrom     = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo       = now()->format('Y-m-d');
    }

    public function getUsersProperty(): Collection
    {
        return User::whereIn('id', PriceChange::distinct()->pluck('user_id'))->orderBy('name')->get(['id', 'name']);
    }

    public function getEntriesProperty(): Collection
    {
        return PriceChange::query()
            ->with('user:id,name')
            ->when($this->filterUserId, fn ($q) => $q->where('user_id', $this->filterUserId))
            ->when($this->filterKind, fn ($q) => $q->where('kind', $this->filterKind))
            ->when($this->dateFrom, fn ($q) => $q->where('created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay()))
            ->when($this->dateTo, fn ($q) => $q->where('created_at', '<=', Carbon::parse($this->dateTo)->endOfDay()))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->get();
    }

    /** Umumiy statistika: jami, oshirilgan, kamaytirilgan, xizmat qo'shish/olib tashlash, hodimlar bo'yicha */
    public function stats(Collection $rows): array
    {
        $up   = $rows->filter(fn ($r) => $r->delta() > 0);
        $down = $rows->filter(fn ($r) => $r->delta() < 0);

        return [
            'count'    => $rows->count(),
            'projects' => $rows->pluck('project_number')->unique()->count(),
            'upSum'    => $up->sum(fn ($r) => $r->delta()),
            'upCount'  => $up->count(),
            'downSum'  => $down->sum(fn ($r) => -$r->delta()),
            'downCount'=> $down->count(),
            'net'      => $rows->sum(fn ($r) => $r->delta()),
            'added'    => $rows->where('kind', 'added')->count(),
            'removed'  => $rows->where('kind', 'removed')->count(),
            'byUser'   => $rows->groupBy('user_id')->map(fn ($g) => [
                'name'  => $g->first()->user?->name ?? "O'chirilgan foydalanuvchi",
                'count' => $g->count(),
                'up'    => $g->sum(fn ($r) => max(0, $r->delta())),
                'down'  => $g->sum(fn ($r) => max(0, -$r->delta())),
                'last'  => $g->max('created_at'),
            ])->sortByDesc('count')->values(),
            'byDay'    => $rows->groupBy(fn ($r) => $r->created_at->format('Y-m-d'))->map->count()->sortKeysDesc(),
        ];
    }
}
