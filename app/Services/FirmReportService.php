<?php

namespace App\Services;

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
        $completed = ProjectService::with('assignedUser')
            ->whereNotNull('completed_at')
            ->whereNotNull('assigned_user_id')
            ->whereHas('project', fn ($q) =>
                $q->whereYear('created_at', $year)->whereMonth('created_at', $mon)
                  ->where('status', '!=', 'bekor_qilingan'))
            ->get();

        $jamiTushum     = 0.0;
        $hodimlarUlushi = 0.0;
        $employeeComm   = [];   // uid => ['name' => ..., 'commission' => ...]
        $projIds        = [];

        foreach ($completed as $s) {
            if (!$s->assignedUser) continue;

            $origPrice  = (float) $s->price;
            $finalPrice = (float) $s->final_price;
            $rate  = (float) ($s->assignedUser->commission_rate ?? 20);
            if (in_array($s->assignedUser->role, ['admin', 'menejer'])) $rate = 0;

            // Chegirma to'liq xodim komissiyasidan ayiriladi — firma ulushi
            // (jamiTushum - hodimlarUlushi) shu bilan asl narxdan hisoblangan
            // ulushicha qoladi, chegirmadan ta'sirlanmaydi (BalanceService bilan
            // bir xil mantiq — Mening balansim'dagi summalarga mos keladi).
            $discountAmount = max(0, $origPrice - $finalPrice);
            $comm = round($origPrice * $rate / 100, 0) - $discountAmount;

            $jamiTushum     += $finalPrice;
            $hodimlarUlushi += $comm;
            $projIds[$s->project_id] = true;

            $uid = $s->assigned_user_id;
            if (!isset($employeeComm[$uid])) {
                $employeeComm[$uid] = ['name' => $s->assignedUser->name, 'commission' => 0.0];
            }
            $employeeComm[$uid]['commission'] += $comm;
        }

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

        // Umumiy loyihalar (barcha)
        $allProjectsCount = (int)   Project::count();
        $allProjectsSum   = (float) Project::sum('total_price');

        // Qilinmagan (arxivda emas) loyihalar
        $pendingQuery = Project::whereNotIn('status', $archiveStatuses);
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
