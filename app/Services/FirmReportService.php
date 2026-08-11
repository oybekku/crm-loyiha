<?php

namespace App\Services;

use App\Models\EmployeeSalaryPayment;
use App\Models\Project;
use App\Models\ProjectService;

class FirmReportService
{
    /**
     * Berilgan oy uchun firma hisoboti ma'lumotlari.
     * Dashboard va Oylik hisobot bir xil manbadan foydalanadi.
     */
    public static function forMonth(string $month): array
    {
        [$year, $mon] = array_pad(explode('-', $month), 2, null);
        $archiveStatuses = ['tugallangan', 'taqdim_etilgan', 'bekor_qilingan'];

        // Komissiya HAR BAJARILGAN (tugatilgan) ish bo'yicha — LOYIHA qaysi oyda ochilgan
        // bo'lsa, o'sha oy hisobotiga tushadi (xizmat qachon biriktirilgan/tugatilganidan
        // qat'i nazar). Shu bilan har oyning loyihalar soni/summasi va hodimlar hisoboti
        // doim mos keladi — chalkashlik bo'lmaydi. Bekor qilingan loyiha hisobga olinmaydi.
        $completed = ProjectService::with(['assignedUser', 'project'])
            ->whereNotNull('completed_at')
            ->whereNotNull('assigned_user_id')
            ->whereHas('project', fn ($q) =>
                $q->whereYear('created_at', $year)->whereMonth('created_at', $mon)
                  ->where('status', '!=', 'bekor_qilingan'))
            ->get();

        $jamiTushum     = 0.0;
        $hodimlarUlushi = 0.0;
        $employeeComm   = [];   // uid => ['name','commission'(hisoblangan),'payable'(mijoz to'lagan ulushга mutanosib)]
        $projIds        = [];

        foreach ($completed as $s) {
            if (!$s->assignedUser || !$s->project) continue;

            $calc = EmployeePayableService::commissionForService($s, $s->project);
            $price    = $calc['price'];
            $comm     = $calc['commission'];
            $commPaid = $calc['comm_paid']; // mijoz to'lagan ulushga mutanosib "ochilgan" komissiya

            $jamiTushum     += $price;
            $hodimlarUlushi += $comm;
            $projIds[$s->project_id] = true;

            $uid = $s->assigned_user_id;
            if (!isset($employeeComm[$uid])) {
                $employeeComm[$uid] = ['name' => $s->assignedUser->name, 'commission' => 0.0, 'payable' => 0.0];
            }
            $employeeComm[$uid]['commission'] += $comm;
            $employeeComm[$uid]['payable']    += $commPaid;
        }

        // Hodimga HAQIQATDA berilgan ish haqi to'lovlari (shu oy) — EmployeeSalaryPayment.
        $paidByUser = EmployeeSalaryPayment::where('month', $month)
            ->get()
            ->groupBy('user_id')
            ->map(fn ($g) => (float) $g->sum('amount'));

        $toLanishiKerakJami = 0.0;
        foreach ($employeeComm as $uid => &$emp) {
            $paid   = (float) ($paidByUser[$uid] ?? 0);
            $kerak  = max(0, $emp['payable'] - $paid);
            $emp['paid']  = $paid;
            $emp['kerak'] = $kerak;
            $toLanishiKerakJami += $kerak;
        }
        unset($emp);

        $firmaDaromadi = $jamiTushum - $hodimlarUlushi;
        $toLanganCount = count($projIds);

        // Faol (arxivda emas) loyihalarga biriktirilgan hodimlarni ham qo'shamiz —
        // ishi tugamaganlar 0 bilan ko'rinadi (masalan Qodirxoja: 0)
        $activeAssignees = ProjectService::with('assignedUser')
            ->whereNotNull('assigned_user_id')
            ->whereHas('project', fn ($q) =>
                $q->whereYear('created_at', $year)->whereMonth('created_at', $mon))
            ->get()
            ->pluck('assignedUser')
            ->filter()
            ->unique('id');

        foreach ($activeAssignees as $u) {
            if (in_array($u->role, ['admin', 'menejer'])) continue;
            if (!isset($employeeComm[$u->id])) {
                $employeeComm[$u->id] = ['name' => $u->name, 'commission' => 0.0];
            }
        }

        uasort($employeeComm, fn ($a, $b) => $b['commission'] <=> $a['commission']);

        // Umumiy loyihalar (barcha) — vaqtincha to'xtatilganlar statistikadan chiqarilgan
        $allProjectsCount = (int)   Project::excludePaused()->count();
        $allProjectsSum   = (float) Project::excludePaused()->sum('total_price');

        // Qilinmagan (arxivda emas) loyihalar
        $pendingQuery = Project::whereNotIn('status', $archiveStatuses)->excludePaused();
        $pendingSum   = (float) (clone $pendingQuery)->sum('total_price');
        $pendingPaid  = (float) (clone $pendingQuery)->sum('paid_amount');
        $pendingDebt  = $pendingSum - $pendingPaid;
        $pendingPct   = $pendingSum > 0 ? (int) round($pendingPaid / $pendingSum * 100) : 0;
        $pendingCount = (int) (clone $pendingQuery)->count();

        return [
            'month'            => $month,
            'jamiTushum'       => $jamiTushum,
            'hodimlarUlushi'   => $hodimlarUlushi,
            'firmaDaromadi'    => $firmaDaromadi,
            'toLanganCount'    => $toLanganCount,
            'employeeComm'     => array_values($employeeComm),
            'toLanishiKerakJami' => $toLanishiKerakJami,
            'allProjectsCount' => $allProjectsCount,
            'allProjectsSum'   => $allProjectsSum,
            'pendingCount'     => $pendingCount,
            'pendingSum'       => $pendingSum,
            'pendingPaid'      => $pendingPaid,
            'pendingDebt'      => $pendingDebt,
            'pendingPct'       => $pendingPct,
        ];
    }
}
