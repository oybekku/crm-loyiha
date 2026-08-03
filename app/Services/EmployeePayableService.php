<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Project;
use App\Models\ProjectService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Xodimning komissiyasi (hodim ulushi) hisob-kitobi — Oylik hisobot va
 * Buxgalteriya sahifalarida bir xil natija chiqishi uchun yagona manba.
 * Buxgalteriyadagi "Xarajatlar hisobi" (is_expense_account) belgilangan
 * hisobga har bir hodim uchun avtomatik Expense qatori shu servisdan
 * yoziladi/yangilanadi, shuning uchun Oylik hisobotdagi "To'lanishi kerak"
 * o'zgarsa, xarajat ham darhol sinxron yangilanadi.
 */
class EmployeePayableService
{
    public static function commissionForService(ProjectService $service, ?Project $project): array
    {
        $user = $service->assignedUser;
        $rate = (float) ($user?->commission_rate ?? 20);
        if ($user && in_array($user->role, ['admin', 'menejer'])) {
            $rate = 0;
        }

        $price      = (float) $service->final_price;
        $commission = round($price * $rate / 100, 2);

        $projTotal = (float) ($project->total_price ?? 0);
        $projPaid  = (float) ($project->paid_amount ?? 0);
        $paidRatio = $projTotal > 0 ? min(1, $projPaid / $projTotal) : 0;

        $commPaid      = round($commission * $paidRatio, 0);
        $commRemaining = max(0, $commission - $commPaid);

        return [
            'rate'           => $rate,
            'price'          => $price,
            'commission'     => $commission,
            'paid_ratio'     => $paidRatio,
            'comm_paid'      => $commPaid,
            'comm_remaining' => $commRemaining,
        ];
    }

    /**
     * Tanlangan oy ('Y-m') uchun har bir hodimning "To'lanishi kerak" summasi
     * — Oylik hisobot detalidagi bilan bir xil formula (mijoz to'lagan ulushga
     * mutanosib ochilgan komissiya yig'indisi, faqat tugatilgan ishlar bo'yicha).
     *
     * @return Collection<int, array{user: \App\Models\User, amount: float}>
     */
    public static function forMonth(string $month): Collection
    {
        [$year, $mon] = explode('-', $month);

        $completedServices = ProjectService::with(['assignedUser', 'project'])
            ->whereNotNull('completed_at')
            ->whereNotNull('assigned_user_id')
            ->whereHas('project', fn ($q) => $q->whereYear('created_at', $year)
                ->whereMonth('created_at', $mon)
                ->where('status', '!=', 'bekor_qilingan'))
            ->get();

        $payable = [];

        foreach ($completedServices as $service) {
            $user    = $service->assignedUser;
            $project = $service->project;
            if (!$user || !$project) continue;

            $calc = self::commissionForService($service, $project);

            if (!isset($payable[$user->id])) {
                $payable[$user->id] = ['user' => $user, 'amount' => 0.0];
            }
            $payable[$user->id]['amount'] += $calc['comm_paid'];
        }

        return collect($payable)
            ->filter(fn ($p) => $p['amount'] > 0)
            ->sortBy(fn ($p) => $p['user']->name)
            ->values();
    }

    /**
     * Berilgan oy uchun hisoblangan "To'lanishi kerak" summalarini $accountId
     * hisobiga real Expense qatorlari sifatida yozib/yangilab qo'yadi (bitta
     * hodim + bitta oy = bitta qator, account_id+user_id+month bo'yicha
     * upsert). Endi kerak bo'lmagan (masalan summasi 0 ga tushgan) qatorlar
     * o'chiriladi. Buxgalteriya sahifasi har ochilganda chaqiriladi — shu
     * bilan hisob balansi doim Oylik hisobotdagi summa bilan mos keladi.
     */
    public static function syncExpenses(string $month, int $accountId): void
    {
        $payables      = self::forMonth($month);
        $activeUserIds = $payables->pluck('user.id')->all();

        Expense::where('account_id', $accountId)
            ->where('month', $month)
            ->whereNotNull('user_id')
            ->whereNotIn('user_id', $activeUserIds)
            ->delete();

        [$year, $mon] = explode('-', $month);
        $expenseDate  = Carbon::create((int) $year, (int) $mon, 1)->endOfMonth()->toDateString();

        foreach ($payables as $p) {
            Expense::updateOrCreate(
                ['account_id' => $accountId, 'user_id' => $p['user']->id, 'month' => $month],
                [
                    'amount'       => $p['amount'],
                    'comment'      => $p['user']->name . " — {$month} oyi komissiyasi (avtomatik)",
                    'expense_date' => $expenseDate,
                ]
            );
        }
    }
}
