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

        $totalCount   = Project::count();
        $yangiCount   = Project::where('status', 'yangi')->count();
        $jarayonCount = Project::whereIn('status', ['tolov_jarayonida', 'tekshirish'])->count();
        $doneCount    = Project::where('status', 'tugallangan')->count();
        $totalSum     = (float) Project::sum('total_price');
        $paidSum      = (float) Project::sum('paid_amount');
        $debtSum      = $totalSum - $paidSum;
        $paidPct      = $totalSum > 0 ? round(($paidSum / $totalSum) * 100) : 0;
        $overdueCount = Project::whereNotIn('status', ['tugallangan', 'bekor_qilingan', 'arxiv'])
            ->whereNotNull('deadline_date')
            ->where('deadline_date', '<', now()->startOfDay())
            ->count();

        return [
            'userName'      => $user?->name ?? 'Foydalanuvchi',
            'userRole'      => $user?->role_name ?? ucfirst($user?->role ?? ''),
            'totalCount'    => $totalCount,
            'yangiCount'    => $yangiCount,
            'activeCount'   => Project::whereNotIn('status', ['tugallangan', 'bekor_qilingan', 'arxiv'])->count(),
            'doneCount'     => $doneCount,
            'quote'         => $quotes[now()->dayOfYear % count($quotes)],
            'monthlyIncome' => $monthlyIncome,
            'maxIncome'     => $maxIncome,
            'currentMonth'  => (int) now()->month,
            'recentProjects'=> $recentProjects,
            // Bottom stats
            'statProjects'  => $totalCount,
            'statYangi'     => $yangiCount,
            'statJarayon'   => $jarayonCount,
            'statDone'      => $doneCount,
            'statTotalSum'  => $totalSum,
            'statPaidSum'   => $paidSum,
            'statDebt'      => $debtSum,
            'statPaidPct'   => $paidPct,
            'statOverdue'   => $overdueCount,
        ];
    }
}
