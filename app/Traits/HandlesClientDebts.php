<?php

namespace App\Traits;

use App\Models\Project;
use Illuminate\Support\Collection;

/**
 * "Mijozlar qarzlari" jadvali — Yangi bux tabi va alohida "Mijozlar qarzlari"
 * sahifasi (menejerlar ham ko'radi) shu traitdan foydalanadi.
 * Jadvalning o'zi: resources/views/filament/pages/partials/client-debts.blade.php
 */
trait HandlesClientDebts
{
    // Oy filtri: '' = umumiy (barcha oylar), 'YYYY-MM' = shu oyda ochilgan loyihalar
    public string $debtMonth = '';

    public static function canManageClientDebts(): bool
    {
        $u = auth()->user();
        return (bool) ($u?->isAdmin() || $u?->isMenejer());
    }

    // Query builder orqali yoziladi — Project model hodisalari (status log,
    // SMS va h.k.) bu oddiy belgilar uchun ishga tushmasin.
    public function saveDebtComment(int $projectId, ?string $comment): void
    {
        if (!static::canManageClientDebts()) return;
        $comment = trim((string) $comment);
        Project::whereKey($projectId)->update(['debt_comment' => $comment !== '' ? mb_substr($comment, 0, 2000) : null]);
        $this->dispatch('notify', type: 'success', message: 'Izoh saqlandi');
    }

    public function toggleDebtCalled(int $projectId): void
    {
        if (!static::canManageClientDebts()) return;
        $p = Project::find($projectId, ['id', 'debt_called']);
        if (!$p) return;

        if ($p->debt_called) {
            // "Qilinmadi"ga qaytariladi — oxirgi qo'ng'iroq sanasi esa saqlanib qoladi
            Project::whereKey($projectId)->update(['debt_called' => false]);
        } else {
            Project::whereKey($projectId)->update([
                'debt_called'    => true,
                'debt_called_at' => now(),
                'debt_called_by' => auth()->id(),
            ]);
            $this->dispatch('notify', type: 'success', message: 'Telefon qilindi deb belgilandi');
        }
    }

    /** Qarzdor loyihalar (bekor qilinmagan, to'xtatilmagan), eng katta qarzdan boshlab. */
    protected function clientDebts(): Collection
    {
        return Project::excludePaused()
            ->where('status', '!=', 'bekor_qilingan')
            ->whereColumn('total_price', '>', 'paid_amount')
            ->with('debtCalledBy:id,name')
            ->get(['id', 'seq_no', 'owner_name', 'title', 'address', 'total_price', 'paid_amount', 'created_at', 'status',
                   'phones', 'debt_comment', 'debt_called', 'debt_called_at', 'debt_called_by'])
            ->map(fn ($p) => [
                'project' => $p,
                'debt'    => max(0, (float) $p->total_price - (float) $p->paid_amount),
            ])
            ->filter(fn ($r) => $r['debt'] > 0)
            ->sortByDesc('debt')
            ->values();
    }

    /**
     * Jadval uchun ma'lumot: tanlangan oy bo'yicha qarzlar + oy tanlovi
     * (faqat qarzdor loyihasi bor oylar, eng yangisi birinchi, soni va summasi bilan).
     */
    protected function clientDebtsViewData(?Collection $all = null): array
    {
        $all ??= $this->clientDebts();

        $months = $all->groupBy(fn ($r) => $r['project']->created_at?->format('Y-m') ?? '')
            ->filter(fn ($g, $k) => $k !== '')
            ->map(fn ($g, $k) => [
                'label' => ['Yanvar', 'Fevral', 'Mart', 'Aprel', 'May', 'Iyun', 'Iyul', 'Avgust', 'Sentabr', 'Oktabr', 'Noyabr', 'Dekabr'][(int) substr($k, 5, 2) - 1] . ' ' . substr($k, 0, 4),
                'count' => $g->count(),
                'sum'   => (float) $g->sum('debt'),
            ])
            ->sortKeysDesc();

        if ($this->debtMonth !== '' && !$months->has($this->debtMonth)) {
            $this->debtMonth = ''; // o'sha oyda qarz qolmagan — umumiyga qaytamiz
        }

        $rows = $this->debtMonth === ''
            ? $all
            : $all->filter(fn ($r) => $r['project']->created_at?->format('Y-m') === $this->debtMonth)->values();

        return [
            'debtRows'     => $rows,
            'debtMonths'   => $months,
            'debtRowsSum'  => (float) $rows->sum('debt'),
            'debtAllSum'   => (float) $all->sum('debt'),
            'debtAllCount' => $all->count(),
        ];
    }
}
