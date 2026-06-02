<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\Project;
use Filament\Widgets\Widget;
use Filament\Widgets\StatsOverviewWidget;

class WelcomeHeroWidget extends Widget
{
    protected static string $view = 'filament.widgets.welcome-hero';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = -2;

    public function getViewData(): array
    {
        $user = auth()->user();
        $year = now()->year;

        $monthlyIncome = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyIncome[] = (float) Payment::whereYear('created_at', $year)
                ->whereMonth('created_at', $m)
                ->sum('amount');
        }
        $maxIncome = max($monthlyIncome) ?: 1;

        $quotes = [
            "Har bir loyiha — kelajakka yozilgan xat.",
            "Buyuk binolar buyuk qarorlardan boshlanadi.",
            "Muvaffaqiyat rejadan boshlanib, ishchanlikda tugaydi.",
            "Har bir devor — jamoaning birgalikdagi mehri.",
            "Poydevor qanchalik mustahkam bo'lsa, bino shunchalik baland.",
            "Bitta to'g'ri qaror ming muammoni hal qiladi.",
            "Bugun qilingan ish — ertangi muvaffaqiyatning asosi.",
        ];

        $recentProjects = Project::with('assignedUsers')
            ->latest()
            ->limit(4)
            ->get();

        $isEmployee = $user?->isBajaruvchi();

        // ── Umumiy (admin/menejer uchun) yoki shaxsiy (bajaruvchi uchun) ──
        $baseQuery = $isEmployee
            ? Project::whereHas('assignedUsers', fn($q) => $q->where('users.id', $user->id))
            : Project::query();

        $totalCount   = (clone $baseQuery)->count();
        $yangiCount   = (clone $baseQuery)->where('status', 'yangi')->count();
        $jarayonCount = (clone $baseQuery)->whereIn('status', ['tolov_jarayonida', 'tekshirish'])->count();
        $doneCount    = (clone $baseQuery)->where('status', 'tugallangan')->count();
        $totalSum     = (float) (clone $baseQuery)->sum('total_price');
        $paidSum      = (float) (clone $baseQuery)->sum('paid_amount');
        $debtSum      = $totalSum - $paidSum;
        $paidPct      = $totalSum > 0 ? round(($paidSum / $totalSum) * 100) : 0;
        $overdueProjects = (clone $baseQuery)->whereNotIn('status', ['tugallangan', 'bekor_qilingan', 'arxiv'])
            ->whereNotNull('deadline_date')
            ->where('deadline_date', '<', now()->startOfDay())
            ->select('id', 'number', 'owner_name', 'deadline_date', 'status')
            ->orderBy('deadline_date')
            ->get();
        $overdueCount = $overdueProjects->count();

        // ── Bajaruvchi uchun shaxsiy statistika ──
        $myStats = null;
        if ($isEmployee) {
            $month = now()->month;
            $yr    = now()->year;

            // Bu oyda tugallangan xizmatlar
            $myDoneServices = \App\Models\ProjectService::where('assigned_user_id', $user->id)
                ->whereHas('project', fn($q) => $q->where('status', 'tugallangan'))
                ->whereHas('project.statusLogs', fn($q) => $q
                    ->where('status', 'tugallangan')
                    ->whereYear('entered_at', $yr)
                    ->whereMonth('entered_at', $month)
                )
                ->get();

            // Jarayondagi (tugallanmagan) xizmatlar
            $myPendingServices = \App\Models\ProjectService::where('assigned_user_id', $user->id)
                ->whereHas('project', fn($q) => $q->whereNotIn('status', ['tugallangan', 'taqdim_etilgan', 'bekor_qilingan']))
                ->get();

            $myStats = [
                'done_count'   => $myDoneServices->count(),
                'done_sum'     => (float) $myDoneServices->sum('final_price'),
                'pending_count'=> $myPendingServices->count(),
                'pending_sum'  => (float) $myPendingServices->sum('final_price'),
            ];
        }

        return [
            'userName'      => $user?->name ?? 'Foydalanuvchi',
            'userRole'      => $user?->role_name ?? ucfirst($user?->role ?? ''),
            'isEmployee'    => $isEmployee,
            'myStats'       => $myStats,
            'totalCount'    => $totalCount,
            'yangiCount'    => $yangiCount,
            'activeCount'   => (clone $baseQuery)->whereNotIn('status', ['tugallangan', 'bekor_qilingan', 'arxiv'])->count(),
            'doneCount'     => $doneCount,
            'quote'         => $quotes[now()->dayOfYear % count($quotes)],
            'monthlyIncome' => $monthlyIncome,
            'maxIncome'     => $maxIncome,
            'currentMonth'  => (int) now()->month,
            'recentProjects'=> $recentProjects,
            'statProjects'  => $totalCount,
            'statYangi'     => $yangiCount,
            'statJarayon'   => $jarayonCount,
            'statDone'      => $doneCount,
            'statTotalSum'  => $totalSum,
            'statPaidSum'   => $paidSum,
            'statDebt'      => $debtSum,
            'statPaidPct'   => $paidPct,
            'statOverdue'        => $overdueCount,
            'overdueProjects'    => $overdueProjects,
        ];
    }
}
