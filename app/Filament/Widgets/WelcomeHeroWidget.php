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

    // Tanlangan davr (oy/yil) — loyihalar ochilgan oyiga qarab filtrlanadi
    public ?int $selYear  = null;
    public ?int $selMonth = null;

    public function mount(): void
    {
        $this->selYear  ??= (int) now()->year;
        $this->selMonth ??= (int) now()->month;
    }

    public function selectMonth(int $m): void
    {
        if ($m >= 1 && $m <= 12) $this->selMonth = $m;
    }

    public function changeYear(int $delta): void
    {
        $this->selYear += $delta;
    }

    public function getViewData(): array
    {
        $user = auth()->user();
        $this->selYear  ??= (int) now()->year;
        $this->selMonth ??= (int) now()->month;
        $year = $this->selYear;

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
            ? Project::whereHas('services', fn($q) => $q->where('assigned_user_id', $user->id))
            : Project::query();

        // Tanlangan oy/yil bo'yicha — loyiha OCHILGAN (created_at) oyiga qarab.
        // Shu sababli o'tgan oy loyihalari keyingi oyga "o'tmaydi" (har oy alohida).
        $baseQuery->whereYear('created_at', $this->selYear)
                  ->whereMonth('created_at', $this->selMonth);

        // Arxiv (yakunlangan) bosqichlar — bazadan olinadi, shu sababli yangi status
        // qo'shilsa/o'zgarsa ham statistika hisoblagichlari avtomatik to'g'ri qoladi.
        $archiveStatuses = \App\Models\ProjectStatus::where('is_archive', true)->pluck('key')->all();
        if (empty($archiveStatuses)) $archiveStatuses = ['tugallangan', 'taqdim_etilgan', 'bekor_qilingan'];

        $totalCount   = (clone $baseQuery)->count();
        $yangiCount   = (clone $baseQuery)->where('status', 'yangi')->count();
        // "Jarayonda" — yangi va arxiv bo'lmagan barcha oraliq bosqichlar (masalan:
        // Toposyomka, Eskiz loyiha, To'langan...). Avval faqat 2 ta statusni sanardi,
        // shu sababli loyihalar boshqa bosqichlarga o'tganda hisobdan "yo'qolib qolardi".
        $jarayonCount = (clone $baseQuery)->where('status', '!=', 'yangi')->whereNotIn('status', $archiveStatuses)->count();
        $doneCount    = (clone $baseQuery)->whereIn('status', $archiveStatuses)->count();
        $doneSum      = (float) (clone $baseQuery)->whereIn('status', $archiveStatuses)->sum('total_price');
        $totalSum     = (float) (clone $baseQuery)->sum('total_price');
        $paidSum      = (float) (clone $baseQuery)->sum('paid_amount');
        $debtSum      = $totalSum - $paidSum;
        $paidPct      = $totalSum > 0 ? round(($paidSum / $totalSum) * 100) : 0;

        // Qilinmagan (arxivda emas) loyihalar — "Qilinmagan loyihalar" kartasi uchun
        $pendingQ        = (clone $baseQuery)->whereNotIn('status', $archiveStatuses);
        $pendingCountTop = (int)   (clone $pendingQ)->count();
        $pendingSumTop   = (float) (clone $pendingQ)->sum('total_price');
        $pendingPaidTop  = (float) (clone $pendingQ)->sum('paid_amount');
        $pendingDebtTop  = $pendingSumTop - $pendingPaidTop;
        $pendingPctTop   = $pendingSumTop > 0 ? round($pendingPaidTop / $pendingSumTop * 100) : 0;
        // ── Kechikkan / muddati yaqin ishlar (xizmat-asosli — kanban bilan bir xil) ──
        // Tanlangan oy bo'yicha — loyiha OCHILGAN (created_at) oyiga qarab filtrlanadi.
        // Shu sababli o'tgan oy ishlari keyingi oyga "o'tmaydi" — har oy alohida qoladi.
        $attnQ = \App\Models\ProjectService::query()
            ->whereNotNull('assigned_user_id')
            ->whereNotNull('work_started_at')
            ->whereNull('completed_at')
            ->where('deadline_days', '>', 0)
            // Muzlatilgan (kutish) VA to'lov kutayotgan (tolov_jarayonida/tolangan — ishi tugagan)
            // loyihalar diqqat talab ishlarда ko'rinmaydi
            ->whereHas('project', fn ($q) => $q
                ->whereNotIn('status', array_merge($archiveStatuses, ['tolov_jarayonida', 'tolangan']))
                ->whereNull('timer_paused_at')
                ->whereYear('created_at', $this->selYear)
                ->whereMonth('created_at', $this->selMonth))
            ->with(['project:id,number,owner_name,status', 'assignedUser:id,name']);
        if ($isEmployee) {
            $attnQ->where('assigned_user_id', $user->id);
        }

        $svcLabels    = Project::serviceOptions();
        $overdueItems = [];
        $soonItems    = [];
        foreach ($attnQ->get() as $s) {
            if (!$s->project) continue;
            // Muddat muzlatishni hisobga oladi (submitted_at) — model accessorlari orqali
            $daysLeft = $s->days_left;
            $late     = $s->is_late;
            $row = [
                'project_id' => $s->project_id,
                'number'     => $s->project->number,
                'owner'      => $s->project->owner_name,
                'service'    => $svcLabels[$s->service_name] ?? $s->service_name,
                'user_id'    => $s->assigned_user_id,
                'user_name'  => $s->assignedUser?->name ?? '—',
                'days_left'  => $daysLeft,
                'over_days'  => $s->late_days,
            ];
            if ($late) {
                $overdueItems[] = $row;
            } elseif ($daysLeft <= 3) {
                $soonItems[] = $row;
            }
        }
        // Eng kechikkani / eng yaqini tepada
        usort($overdueItems, fn ($a, $b) => $a['days_left'] <=> $b['days_left']);
        usort($soonItems,    fn ($a, $b) => $a['days_left'] <=> $b['days_left']);

        // Hodimlar bo'yicha guruhlash (admin uchun)
        $groupByEmp = function (array $items) {
            $g = [];
            foreach ($items as $it) {
                $uid = $it['user_id'];
                if (!isset($g[$uid])) $g[$uid] = ['name' => $it['user_name'], 'count' => 0, 'items' => []];
                $g[$uid]['count']++;
                $g[$uid]['items'][] = $it;
            }
            uasort($g, fn ($a, $b) => $b['count'] <=> $a['count']);
            return array_values($g);
        };
        $overdueByEmployee = $groupByEmp($overdueItems);
        $soonByEmployee    = $groupByEmp($soonItems);
        $overdueCount      = count($overdueItems);
        $soonCount         = count($soonItems);

        // ── Bajaruvchi uchun shaxsiy statistika ──
        $myStats = null;
        if ($isEmployee) {
            $month = now()->month;
            $yr    = now()->year;

            $rate = (float) ($user->commission_rate ?? 20);

            // Bu oyda admin tomonidan tugallangan deb belgilangan xizmatlar
            $myDoneServices = \App\Models\ProjectService::where('assigned_user_id', $user->id)
                ->whereNotNull('completed_at')
                ->whereYear('completed_at', $yr)
                ->whereMonth('completed_at', $month)
                ->get();

            // Jarayondagi (completed_at yo'q) xizmatlar
            $myPendingServices = \App\Models\ProjectService::where('assigned_user_id', $user->id)
                ->whereNull('completed_at')
                ->whereHas('project', fn($q) => $q->whereNotIn('status', ['tugallangan', 'taqdim_etilgan', 'bekor_qilingan']))
                ->get();

            $doneSum    = (float) $myDoneServices->sum('final_price');
            $pendingSum = (float) $myPendingServices->sum('final_price');

            $myStats = [
                'done_count'    => $myDoneServices->count(),
                'done_sum'      => round($doneSum * $rate / 100),
                'pending_count' => $myPendingServices->count(),
                'pending_sum'   => round($pendingSum * $rate / 100),
                'rate'          => $rate,
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
            'statDoneSum'   => $doneSum,
            'statTotalSum'  => $totalSum,
            'statPaidSum'   => $paidSum,
            'statDebt'      => $debtSum,
            'statPaidPct'   => $paidPct,
            'statOverdue'        => $overdueCount,
            'statSoon'           => $soonCount,
            'overdueItems'       => $overdueItems,
            'soonItems'          => $soonItems,
            'overdueByEmployee'  => $overdueByEmployee,
            'soonByEmployee'     => $soonByEmployee,
            'statPendingCount'   => $pendingCountTop,
            'statPendingSum'     => $pendingSumTop,
            'statPendingPaid'    => $pendingPaidTop,
            'statPendingDebt'    => $pendingDebtTop,
            'statPendingPct'     => $pendingPctTop,
            'firmReport'         => $user?->isAdmin()
                ? \App\Services\FirmReportService::forMonth(sprintf('%04d-%02d', $this->selYear, $this->selMonth))
                : null,
            'selYear'            => $this->selYear,
            'selMonth'           => $this->selMonth,
            'monthLabel'         => \Carbon\Carbon::create($this->selYear, $this->selMonth, 1)->translatedFormat('F Y'),
        ];
    }
}
