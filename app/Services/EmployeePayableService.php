<?php

namespace App\Services;

use App\Models\AccountTransfer;
use App\Models\EmployeeSalaryPayment;
use App\Models\Expense;
use App\Models\Project;
use App\Models\ProjectService;
use App\Models\User;
use App\Models\UserCommissionRate;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Xodimning komissiyasi (hodim ulushi) hisob-kitobi — Oylik hisobot,
 * Buxgalteriya, Dashboard va "Mening balansim" sahifalarida bir xil
 * natija chiqishi uchun yagona manba. Komissiya foizi ham shu yerdan
 * (rateFor) olinadi — shu bilan foiz o'zgartirilganda faqat tegishli
 * oydan boshlab qo'llanadi, o'tgan oylar o'zgarmaydi (user_commission_rates
 * jadvali orqali, oy bo'yicha "muzlatilgan" tarix).
 * Buxgalteriyadagi "Xarajatlar hisobi" (is_expense_account) belgilangan
 * hisobga har bir hodim uchun avtomatik Expense qatori shu servisdan
 * yoziladi/yangilanadi, shuning uchun Oylik hisobotdagi "To'lanishi kerak"
 * o'zgarsa, xarajat ham darhol sinxron yangilanadi.
 */
class EmployeePayableService
{
    /**
     * Berilgan oy ('Y-m') uchun hodimning komissiya foizi. Admin/menejer
     * har doim 0%. Shu oy (yoki undan oldingi eng yaqin) uchun alohida
     * belgilangan foiz bo'lsa — o'shani, bo'lmasa foydalanuvchining
     * standart (users.commission_rate) foizini qaytaradi.
     */
    public static function rateFor(?User $user, string $month): float
    {
        if (!$user) return 0.0;
        if (in_array($user->role, ['admin', 'menejer'])) return 0.0;

        $override = UserCommissionRate::where('user_id', $user->id)
            ->where('effective_month', '<=', $month)
            ->orderByDesc('effective_month')
            ->value('rate');

        return (float) ($override ?? $user->commission_rate ?? 20);
    }

    public static function commissionForService(ProjectService $service, ?Project $project): array
    {
        $user  = $service->assignedUser;
        $month = $project?->created_at?->format('Y-m') ?? now()->format('Y-m');
        $rate  = self::rateFor($user, $month);

        $price      = (float) $service->final_price;
        $commission = round($price * $rate / 100, 2);

        $paidForService = $project ? self::paidAmountForService($service, $project) : 0.0;
        $paidRatio      = $price > 0 ? min(1, $paidForService / $price) : 0;

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
     * Bitta xizmat uchun mijoz aniq qancha to'lagan — loyihaning UMUMIY
     * to'langan foizidan farqli o'laroq (bu xatoga sabab bo'lgan edi: bitta
     * xodimning to'liq to'langan ishi, loyihadagi BOSHQA xodimning hali
     * to'lanmagan ishi tufayli sun'iy kamaytirilib yuborilardi). Har bir
     * to'lov qaysi xizmat(lar) uchun ekani (Payment.services) bo'yicha,
     * narxga mutanosib taqsimlanadi — "Loyiha tahrirlash" oynasidagi
     * xizmatlar ro'yxatida ko'rsatiladigan foiz bilan AYNAN BIR XIL mantiq
     * (ProjectEditModal::buildEiServices()).
     */
    public static function paidAmountForService(ProjectService $service, Project $project): float
    {
        $project->loadMissing(['services', 'payments']);

        $priceMap = [];
        foreach ($project->services as $s) {
            $priceMap[$s->service_name] = (float) $s->final_price;
        }

        $paid = 0.0;
        foreach ($project->payments as $pay) {
            // Aniq taqsimot mavjud bo'lsa (yangi to'lovlar — ketma-ket/waterfall
            // usulida saqlangan) — o'sha aniq summani ishlatamiz, taxminiy
            // (narx nisbati bo'yicha proporsional) hisoblash shart emas.
            $split = $pay->service_split ?? null;
            if (is_array($split) && array_key_exists($service->service_name, $split)) {
                $paid += (float) $split[$service->service_name];
                continue;
            }

            $svcs = $pay->services ?? [];
            if (empty($svcs) || !in_array($service->service_name, $svcs, true)) {
                continue;
            }

            $sumSel = 0.0;
            foreach ($svcs as $sn) {
                $sumSel += ($priceMap[$sn] ?? 0);
            }

            $sp    = $priceMap[$service->service_name] ?? 0;
            $paid += $sumSel > 0
                ? (float) $pay->amount * ($sp / $sumSel)
                : (float) $pay->amount / count($svcs);
        }

        return $paid;
    }

    /**
     * Bitta hodim uchun, berilgan oyda — Oylik hisobot bilan AYNAN BIR XIL
     * so'rovlar (loyiha ochilgan oyi bo'yicha, loyihalar soni bo'yicha
     * deduplikatsiya qilingan) orqali hisoblangan qisqa statistika. Hodim
     * bosh sahifasidagi shaxsiy statistika ham shu yerdan olinadi — shu
     * bilan Oylik hisobotdagi va hodim ko'radigan raqam har doim mos keladi
     * (avval ular alohida-alohida, boshqa "oy" mezoni bilan hisoblanardi —
     * shu sabab chalkashlik chiqqan edi).
     */
    public static function statsForUser(User $user, string $month): array
    {
        [$year, $mon] = explode('-', $month);
        $archiveStatuses = ['tugallangan', 'taqdim_etilgan', 'bekor_qilingan'];

        $completedServices = ProjectService::with('project')
            ->where('assigned_user_id', $user->id)
            ->whereNotNull('completed_at')
            ->whereHas('project', fn ($q) => $q->whereYear('created_at', $year)
                ->whereMonth('created_at', $mon)
                ->where('status', '!=', 'bekor_qilingan'))
            ->get();

        $commission = 0.0;
        $clientPaid = 0.0;
        $projectIds = [];
        foreach ($completedServices as $service) {
            if (!$service->project) continue;
            $calc = self::commissionForService($service, $service->project);
            $commission += $calc['commission'];
            $clientPaid += $calc['comm_paid'];
            $projectIds[$service->project_id] = true;
        }

        $pendingServices = ProjectService::with('project')
            ->where('assigned_user_id', $user->id)
            ->whereNull('completed_at')
            ->whereHas('project', fn ($q) => $q->whereNotIn('status', $archiveStatuses)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $mon))
            ->get();

        $pendingCommission = 0.0;
        foreach ($pendingServices as $service) {
            if (!$service->project) continue;
            $pendingCommission += self::commissionForService($service, $service->project)['commission'];
        }

        return [
            'project_count'      => count($projectIds),
            'done_services'      => $completedServices->count(),
            'commission'         => $commission,
            'client_paid'        => $clientPaid,
            'pending_count'      => $pendingServices->count(),
            'pending_commission' => $pendingCommission,
        ];
    }

    /**
     * Tanlangan oy ('Y-m') uchun har bir hodimga tegishli xarajat summasi —
     * "to'lanishi kerak" (mijoz to'lagan ulushga mutanosib ochilgan komissiya,
     * hali haqiqatda berilmagan bo'lsa ham — majburiyat sifatida) va HAQIQATDA
     * berilgan oylik (EmployeeSalaryPayment) summasidan KATTASI. Bunisi kerak,
     * chunki ortiqcha to'lov qilingan hodimlarda haqiqiy chiqim "to'lanishi
     * kerak"dan katta bo'lib qoladi — shunda ham Buxgalteriya haqiqiy chiqqan
     * pulni to'liq ko'rsatishi kerak, "Jami balans" haqiqatdan yaxshiroq
     * ko'rinib qolmasligi uchun.
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

        $actualPaid = EmployeeSalaryPayment::where('month', $month)
            ->get()
            ->groupBy('user_id')
            ->map(fn ($g) => (float) $g->sum('amount'));

        foreach ($actualPaid as $uid => $paidAmount) {
            if (!isset($payable[$uid])) {
                $user = User::find($uid);
                if (!$user) continue;
                $payable[$uid] = ['user' => $user, 'amount' => 0.0];
            }
            $payable[$uid]['amount'] = max($payable[$uid]['amount'], $paidAmount);
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

    /**
     * Xarajat hisobiga yozilgan komissiyalar haqiqatda qaysi hisobdan
     * (masalan "Naqd pul") to'langanini aks ettirish uchun, o'sha oy
     * jami komissiya summasida $sourceAccountId'dan $expenseAccountId'ga
     * avtomatik "o'tkazma" yozib/yangilab qo'yadi. Shu bilan manba hisob
     * balansi real kamayadi, xarajat hisobi esa kirim=chiqim bo'lib 0 da
     * qoladi (faqat "kimga qancha" tafsilotini ko'rsatish uchun xizmat
     * qiladi). syncExpenses() bilan bir xil joydan, bir xil vaqtda
     * chaqiriladi — shuning uchun ikkalasi doim sinxron.
     */
    public static function syncCommissionTransfer(string $month, int $sourceAccountId, int $expenseAccountId): void
    {
        $total = (float) self::forMonth($month)->sum('amount');

        if ($sourceAccountId === $expenseAccountId || $total <= 0) {
            AccountTransfer::where('from_account_id', $sourceAccountId)
                ->where('to_account_id', $expenseAccountId)
                ->where('month', $month)
                ->delete();

            return;
        }

        [$year, $mon] = explode('-', $month);
        $transferDate = Carbon::create((int) $year, (int) $mon, 1)->endOfMonth()->toDateString();

        AccountTransfer::updateOrCreate(
            ['from_account_id' => $sourceAccountId, 'to_account_id' => $expenseAccountId, 'month' => $month],
            [
                'amount'        => $total,
                'comment'       => "{$month} oyi xodimlar komissiyasi (avtomatik)",
                'transfer_date' => $transferDate,
            ]
        );
    }
}
