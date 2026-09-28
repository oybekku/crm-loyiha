<?php

namespace App\Services;

use App\Models\Expense;

/**
 * Xodim bo'yicha xarajatlar ("shu odamga shu oyda qancha xarajat qilindi").
 * Bu PUL HISOBI EMAS — xarajat haqiqatan qaysi hisobdan (Naqd/Karta/Bank)
 * chiqqan bo'lsa, o'sha hisobda qoladi; bu yerda faqat "kim uchun" belgisi
 * (expenses.responsible_id) bo'yicha yig'iladi. Oylikdan avtomatik yozilgan
 * qatorlar (responsible_id bo'sh, user_id = xodim) ham o'sha xodimga kiradi.
 * Eski Buxgalteriya va "Yangi bux" bir xil natija ko'rsatishi uchun yagona joy.
 */
class PersonExpenseService
{
    /**
     * @param  int[]  $userIds
     * @return array<int, array{total: float, count: int, by: array<string, float>}>
     */
    public static function forMonth(int $year, int $month, array $userIds): array
    {
        if (empty($userIds)) return [];

        $ym = sprintf('%04d-%02d', $year, $month);
        $rows = Expense::with('account:id,name,type')
            ->where(function ($q) use ($year, $month, $ym) {
                $q->where('month', $ym)
                    ->orWhere(fn ($q2) => $q2->whereNull('month')->whereYear('expense_date', $year)->whereMonth('expense_date', $month));
            })
            ->where(function ($q) use ($userIds) {
                $q->whereIn('responsible_id', $userIds)
                    ->orWhere(fn ($q2) => $q2->whereNull('responsible_id')->whereIn('user_id', $userIds));
            })
            ->get(['id', 'account_id', 'amount', 'responsible_id', 'user_id']);

        $out = [];
        foreach ($userIds as $uid) $out[$uid] = ['total' => 0.0, 'count' => 0, 'by' => []];
        foreach ($rows as $e) {
            $uid = $e->responsible_id ?: $e->user_id;
            if (!isset($out[$uid])) continue;
            $label = $e->account?->name ?: '—';
            $out[$uid]['total'] += (float) $e->amount;
            $out[$uid]['count']++;
            $out[$uid]['by'][$label] = ($out[$uid]['by'][$label] ?? 0) + (float) $e->amount;
        }
        foreach ($out as &$o) arsort($o['by']);

        return $out;
    }
}
