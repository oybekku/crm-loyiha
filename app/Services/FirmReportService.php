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

        // Komissiya HAR BAJARILGAN (tugatilgan) ish bo'yicha — lekin xizmat AYNAN QAYSI OYDA
        // OCHILGAN/BIRIKTIRILGAN bo'lsa (ProjectService.created_at), o'sha oy hisobotiga
        // tushadi — loyiha qachon ochilganidan yoki ish qachon tugatilganidan qat'i nazar.
        // Masalan: iyunda biriktirilgan ish iyulda tugatilsa, statistikasi IYUN oyiga yoziladi.
        // Bekor qilingan loyiha hisobga olinmaydi.
        $completed = ProjectService::with('assignedUser')
            ->whereNotNull('completed_at')
            ->whereNotNull('assigned_user_id')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $mon)
            ->whereHas('project', fn ($q) => $q->where('status', '!=', 'bekor_qilingan'))
            ->get();

        $jamiTushum     = 0.0;
        $hodimlarUlushi = 0.0;
        $employeeComm   = [];   // uid => ['name' => ..., 'commission' => ...]
        $projIds        = [];

        foreach ($completed as $s) {
            if (!$s->assignedUser) continue;

            $price = (float) $s->final_price;
            $rate  = (float) ($s->assignedUser->commission_rate ?? 20);
            if (in_array($s->assignedUser->role, ['admin', 'menejer'])) $rate = 0;
            $comm = round($price * $rate / 100, 0);

            $jamiTushum     += $price;
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
