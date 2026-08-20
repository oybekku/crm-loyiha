<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Services\EmployeePayableService;
use Filament\Widgets\Widget;
use Filament\Widgets\StatsOverviewWidget;

class WelcomeHeroWidget extends Widget
{
    protected static string $view = 'filament.widgets.welcome-hero';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = -2;

    // Xisobchi faqat "Yangi loyihalar (DIDOX)" navbatini ko'rishi kerak —
    // moliyaviy umumiy statistika unga ko'rinmasligi shart.
    public static function canView(): bool
    {
        return !(auth()->user()?->isHisobchi() ?? false);
    }

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

    // Xodimlar yuklamasi — raqamga bosilganda aynan qaysi ishlar ekanini
    // ro'yxat qilib ko'rsatuvchi modal.
    public bool   $workloadModalOpen  = false;
    public string $workloadModalTitle = '';
    public array  $workloadModalItems = [];

    public function closeWorkloadModal(): void
    {
        $this->workloadModalOpen = false;
    }

    // Ro'yxatdagi (Xodimlar yuklamasi / Diqqat talab ishlar) loyihaga bosilganda —
    // sahifadan chiqmasdan, shu yerning o'zida to'liq tahrirlash modalini ochamiz
    // (Kanban doskasidagi bilan bir xil komponent — resources/views/filament/pages/dashboard.blade.php
    // ichida @livewire('project-edit-modal') qo'shilgan).
    public function openProjectEdit(int $projectId): void
    {
        $this->workloadModalOpen = false;
        $this->dispatch('open-edit-modal', id: $projectId);
    }

    public function showWorkloadItems(int $userId, string $bucket, ?int $month = null): void
    {
        if ($bucket === 'unassigned') {
            $q = \App\Models\ProjectService::whereNull('assigned_user_id')
                ->with('project:id,seq_no,number,owner_name');
            $titleName = 'Biriktirilmagan ishlar';
        } else {
            $user = \App\Models\User::find($userId);
            if (!$user) return;
            $q = \App\Models\ProjectService::where('assigned_user_id', $userId)
                ->with('project:id,seq_no,number,owner_name');
            $titleName = $user->name;
        }

        $titleSuffix = 'Jami';
        switch ($bucket) {
            case 'completed':
                $q->whereNotNull('completed_at');
                $titleSuffix = 'Yakunlangan';
                break;
            case 'inprogress':
                $q->whereNotNull('work_started_at')->whereNull('completed_at')
                    ->whereHas('project', fn ($p) => $p->whereNull('timer_paused_at'));
                $titleSuffix = 'Jarayonda';
                break;
            case 'paused':
                $q->whereNotNull('work_started_at')->whereNull('completed_at')
                    ->whereHas('project', fn ($p) => $p->whereNotNull('timer_paused_at'));
                $titleSuffix = 'Muzlatilgan';
                break;
            case 'month_done':
                $q->whereNotNull('work_started_at')
                    ->whereYear('work_started_at', $this->selYear)
                    ->whereMonth('work_started_at', $month)
                    ->whereNotNull('completed_at');
                $titleSuffix = 'Tugallangan — ' . \Carbon\Carbon::create($this->selYear, $month, 1)->translatedFormat('F');
                break;
            case 'month_prog':
                $q->whereNotNull('work_started_at')
                    ->whereYear('work_started_at', $this->selYear)
                    ->whereMonth('work_started_at', $month)
                    ->whereNull('completed_at')
                    ->whereHas('project', fn ($p) => $p->whereNull('timer_paused_at'));
                $titleSuffix = 'Jarayonda — ' . \Carbon\Carbon::create($this->selYear, $month, 1)->translatedFormat('F');
                break;
            case 'month_paused':
                $q->whereNotNull('work_started_at')
                    ->whereYear('work_started_at', $this->selYear)
                    ->whereMonth('work_started_at', $month)
                    ->whereNull('completed_at')
                    ->whereHas('project', fn ($p) => $p->whereNotNull('timer_paused_at'));
                $titleSuffix = 'Muzlatilgan — ' . \Carbon\Carbon::create($this->selYear, $month, 1)->translatedFormat('F');
                break;
            case 'overdue':
                $q->whereNotNull('work_started_at')->whereNull('completed_at')
                    ->where('deadline_days', '>', 0)
                    ->whereHas('project', fn ($p) => $p->whereNull('timer_paused_at'));
                $titleSuffix = 'Kechikayotgan';
                break;
            case 'month_overdue':
                $q->whereNotNull('work_started_at')
                    ->whereYear('work_started_at', $this->selYear)
                    ->whereMonth('work_started_at', $month)
                    ->whereNull('completed_at')
                    ->where('deadline_days', '>', 0)
                    ->whereHas('project', fn ($p) => $p->whereNull('timer_paused_at'));
                $titleSuffix = 'Kechikayotgan — ' . \Carbon\Carbon::create($this->selYear, $month, 1)->translatedFormat('F');
                break;
        }

        $svcLabels = Project::serviceOptions();
        $collection = $q->orderByDesc('work_started_at')->get();
        if (in_array($bucket, ['overdue', 'month_overdue'], true)) {
            $collection = $collection->filter(fn ($s) => $s->is_late)->values();
        }
        $items = $collection->map(function ($s) use ($svcLabels) {
            $status = 'Boshlanmagan';
            if ($s->completed_at) {
                $status = 'Tugallangan';
            } elseif ($s->work_started_at) {
                $status = $s->project?->timer_paused_at ? 'Muzlatilgan' : 'Jarayonda';
            }
            return [
                'projectId' => $s->project_id,
                'seq'       => $s->project?->seq_no ?? $s->project?->number ?? '—',
                'owner'     => $s->project?->owner_name ?? '—',
                'service'   => $svcLabels[$s->service_name] ?? $s->service_name,
                'date'      => $s->work_started_at?->format('d.m.Y') ?? '—',
                'status'    => $status,
                'lateDays'  => $s->deadline_days > 0 ? (int) $s->late_days : 0,
            ];
        })->toArray();

        $this->workloadModalTitle = $titleName . ' — ' . $titleSuffix . ' (' . count($items) . ' ta)';
        $this->workloadModalItems = $items;
        $this->workloadModalOpen  = true;
    }

    public function getViewData(): array
    {
        $user = auth()->user();
        $this->selYear  ??= (int) now()->year;
        $this->selMonth ??= (int) now()->month;
        $year = $this->selYear;
        $isEmployee = $user?->isBajaruvchi();

        // "Loyiha dinamikasi" grafigi — pastdagi "Jami loyihalar" statistikasi
        // bilan bir xil narsani ko'rsatishi kerak: shu oyda OCHILGAN loyihalar
        // soni (avval to'lovlar summasi ko'rsatilardi — ikkalasi turlicha
        // hikoya aytib, chalkashtirib yuborardi).
        $monthlyCounts = [];
        for ($m = 1; $m <= 12; $m++) {
            $q = $isEmployee
                ? Project::whereHas('services', fn($qq) => $qq->where('assigned_user_id', $user->id))
                : Project::query();
            $monthlyCounts[] = $q->whereYear('created_at', $year)
                ->whereMonth('created_at', $m)
                ->excludePaused()
                ->count();
        }
        $maxCount = max($monthlyCounts) ?: 1;

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

        // ── Umumiy (admin/menejer uchun) yoki shaxsiy (bajaruvchi uchun) ──
        $baseQuery = $isEmployee
            ? Project::whereHas('services', fn($q) => $q->where('assigned_user_id', $user->id))
            : Project::query();

        // Tanlangan oy/yil bo'yicha — loyiha OCHILGAN (created_at) oyiga qarab.
        // Shu sababli o'tgan oy loyihalari keyingi oyga "o'tmaydi" (har oy alohida).
        // Vaqtincha to'xtatilgan ("o'lik") loyihalar statistikaga kirmaydi —
        // ular Kanban board'da ko'rinishda qoladi, lekin hisobotlarga qo'shilmaydi.
        $baseQuery->whereYear('created_at', $this->selYear)
                  ->whereMonth('created_at', $this->selMonth)
                  ->excludePaused();

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

        // Tugallangan (arxiv) loyihalar — pul qayerga ketganini ko'rsatish uchun
        $doneQ     = (clone $baseQuery)->whereIn('status', $archiveStatuses);
        $doneCount = (int)   (clone $doneQ)->count();
        $doneSum   = (float) (clone $doneQ)->sum('total_price');
        $donePaid  = (float) (clone $doneQ)->sum('paid_amount');
        $doneDebt  = $doneSum - $donePaid;
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
                ->excludePaused()
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

        // ── Bajaruvchi uchun shaxsiy statistika — TANLANGAN oy bo'yicha,
        // Oylik hisobot bilan AYNAN BIR XIL manbadan (EmployeePayableService::
        // statsForUser) — avval bu yerda alohida so'rov bor edi (xizmat
        // TUGATILGAN oyi bo'yicha, loyihalar emas xizmatlar soni bo'yicha),
        // shu sabab Oylik hisobotdagi raqamdan farq qilib qolardi.
        $myStats = null;
        if ($isEmployee) {
            $month = $this->selMonth;
            $yr    = $this->selYear;

            $rate  = EmployeePayableService::rateFor($user, sprintf('%04d-%02d', $yr, $month));
            $stats = EmployeePayableService::statsForUser($user, sprintf('%04d-%02d', $yr, $month));

            $myStats = [
                'done_count'    => $stats['project_count'],
                'done_sum'      => round($stats['commission']),
                'client_paid'   => $stats['client_paid'],
                'pending_count' => $stats['pending_count'],
                'pending_sum'   => round($stats['pending_commission']),
                'rate'          => $rate,
            ];
        }

        // Xizmat turlari bo'yicha statistika (tanlangan oy) — masalan:
        // Toposyomka 100 ta 10 000 000, Eskiz loyiha 50 ta 50 000 000...
        $svcTypeQuery = \App\Models\ProjectService::whereHas('project', fn ($q) => $q
            ->whereYear('created_at', $this->selYear)
            ->whereMonth('created_at', $this->selMonth));
        if ($isEmployee) {
            $svcTypeQuery->where('assigned_user_id', $user->id);
        }
        $byServiceType = $svcTypeQuery
            ->selectRaw('service_name, COUNT(*) as cnt, SUM(final_price) as total')
            ->groupBy('service_name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'label' => $svcLabels[$r->service_name] ?? $r->service_name,
                'count' => (int) $r->cnt,
                'total' => (float) $r->total,
            ])
            ->toArray();

        // ── Bugungi naqd tushum — oylarga ajratilgan (menejer/admin uchun) ──
        // Menejer eski oy qarzini yopayotganda to'lov sanasini orqaga
        // o'zgartirib kiritishi mumkin — shu sababli "bugun" filtri
        // created_at bo'yicha (bugun tizimga yozilgan), guruhlash esa
        // payment_date oyiga qarab (qaysi oy qarzi yopilgani).
        $cashTodayGroups   = [];
        $cashTodayTotal    = 0.0;
        $cashTodayUnlinked = 0.0;
        if (!$isEmployee) {
            $cashQ = \App\Models\Payment::query()
                ->with('project:id,owner_name')
                ->where('method', 'naqd')
                ->whereDate('created_at', now());
            if ($user->isAdmin()) {
                $cashQ->with('createdBy:id,name');
            } else {
                $cashQ->where('created_by', $user->id);
            }
            $todayPayments = $cashQ->orderBy('payment_date')->orderBy('created_at')->get();

            $byMonth = [];
            foreach ($todayPayments as $p) {
                $key = $p->payment_date->format('Y-m');
                if (!isset($byMonth[$key])) {
                    $byMonth[$key] = [
                        'label'       => ucfirst($p->payment_date->translatedFormat('F Y')),
                        'sum'         => 0.0,
                        'unlinkedSum' => 0.0,
                        'items'       => [],
                    ];
                }
                $byMonth[$key]['sum'] += (float) $p->amount;
                if (!$p->account_id) $byMonth[$key]['unlinkedSum'] += (float) $p->amount;
                $byMonth[$key]['items'][] = [
                    'fish'     => $p->project?->owner_name ?? '—',
                    'summa'    => (float) $p->amount,
                    'sana'     => $p->payment_date->format('d.m.Y'),
                    'vaqt'     => $p->created_at->format('H:i'),
                    'note'     => $p->note,
                    'manager'  => $user->isAdmin() ? ($p->createdBy?->name ?? '—') : null,
                    'unlinked' => !$p->account_id,
                ];
            }
            krsort($byMonth);
            $cashTodayGroups = array_values($byMonth);
            $cashTodayTotal  = (float) $todayPayments->sum('amount');
            $cashTodayUnlinked = (float) $todayPayments->whereNull('account_id')->sum('amount');
        }

        // ── Xodimlar yuklamasi — mutaxassis bo'yicha jami/yakunlangan/jarayonda/
        // muzlatilgan va tanlangan yilda oylar kesimida ish boshlangan sonlar
        // (admin/menejer). Rolga qaramasdan — kimga bironta ish biriktirilgan
        // bo'lsa (admin ham) shu ro'yxatga tushadi. ──
        $employeeWorkload   = [];
        $unassignedCount    = 0;
        if (!$isEmployee) {
            $assignedIds = \App\Models\ProjectService::whereNotNull('assigned_user_id')
                ->distinct()->pluck('assigned_user_id');
            $employees = \App\Models\User::whereIn('id', $assignedIds)->orderBy('name')->get();

            foreach ($employees as $emp) {
                $svcQ  = \App\Models\ProjectService::where('assigned_user_id', $emp->id);
                $total = (clone $svcQ)->count();
                if ($total === 0) continue;

                $completed  = (clone $svcQ)->whereNotNull('completed_at')->count();
                $inProgress = (clone $svcQ)->whereNotNull('work_started_at')->whereNull('completed_at')
                    ->whereHas('project', fn ($p) => $p->whereNull('timer_paused_at'))->count();
                $paused     = (clone $svcQ)->whereNotNull('work_started_at')->whereNull('completed_at')
                    ->whereHas('project', fn ($p) => $p->whereNotNull('timer_paused_at'))->count();

                // Kechikayotgan — jarayondagilarning ichidan muddati o'tganlari
                // (is_late — accessor, SQL'da hisoblab bo'lmaydi, shu sabab PHP'da filtrlanadi).
                $overdue = (clone $svcQ)->whereNotNull('work_started_at')->whereNull('completed_at')
                    ->where('deadline_days', '>', 0)
                    ->whereHas('project', fn ($p) => $p->whereNull('timer_paused_at'))
                    ->get(['id', 'work_started_at', 'submitted_at', 'deadline_days'])
                    ->filter(fn ($s) => $s->is_late)->count();

                // Har oy uchun to'rtta son: shu oyda boshlangan ishlardan qanchasi
                // hozir tugallangan, qanchasi jarayonda (shundan qanchasi kechikayotgan)
                // va qanchasi muzlatilgan.
                $monthlyDone    = array_fill(1, 12, 0);
                $monthlyProg    = array_fill(1, 12, 0);
                $monthlyOverdue = array_fill(1, 12, 0);
                $monthlyPaused  = array_fill(1, 12, 0);
                (clone $svcQ)->whereNotNull('work_started_at')
                    ->whereYear('work_started_at', $this->selYear)
                    ->with('project:id,timer_paused_at')
                    ->get(['id', 'project_id', 'work_started_at', 'completed_at', 'submitted_at', 'deadline_days'])
                    ->each(function ($s) use (&$monthlyDone, &$monthlyProg, &$monthlyOverdue, &$monthlyPaused) {
                        $m = (int) $s->work_started_at->format('n');
                        if ($s->completed_at) {
                            $monthlyDone[$m]++;
                        } elseif ($s->project?->timer_paused_at) {
                            $monthlyPaused[$m]++;
                        } else {
                            $monthlyProg[$m]++;
                            if ($s->is_late) $monthlyOverdue[$m]++;
                        }
                    });

                $monthly = [];
                for ($m = 1; $m <= 12; $m++) {
                    $monthly[] = [
                        'done'    => $monthlyDone[$m],
                        'prog'    => $monthlyProg[$m],
                        'overdue' => $monthlyOverdue[$m],
                        'paused'  => $monthlyPaused[$m],
                    ];
                }

                $employeeWorkload[] = [
                    'id'         => $emp->id,
                    'name'       => $emp->name,
                    'total'      => $total,
                    'completed'  => $completed,
                    'inProgress' => $inProgress,
                    'overdue'    => $overdue,
                    'paused'     => $paused,
                    'monthly'    => $monthly,
                ];
            }

            $unassignedCount = \App\Models\ProjectService::whereNull('assigned_user_id')->count();
        }

        return [
            'employeeWorkload' => $employeeWorkload,
            'unassignedCount'  => $unassignedCount,
            'cashTodayGroups'   => $cashTodayGroups,
            'cashTodayTotal'    => $cashTodayTotal,
            'cashTodayUnlinked' => $cashTodayUnlinked,
            'userName'      => $user?->name ?? 'Foydalanuvchi',
            'userRole'      => $user?->role_name ?? ucfirst($user?->role ?? ''),
            'isEmployee'    => $isEmployee,
            'myStats'       => $myStats,
            'totalCount'    => $totalCount,
            'yangiCount'    => $yangiCount,
            'activeCount'   => (clone $baseQuery)->whereNotIn('status', ['tugallangan', 'bekor_qilingan', 'arxiv'])->count(),
            'doneCount'     => $doneCount,
            'quote'         => $quotes[now()->dayOfYear % count($quotes)],
            'monthlyCounts' => $monthlyCounts,
            'maxCount'      => $maxCount,
            'currentMonth'  => (int) now()->month,
            'recentProjects'=> $recentProjects,
            'statProjects'  => $totalCount,
            'statYangi'     => $yangiCount,
            'statJarayon'   => $jarayonCount,
            'statDone'      => $doneCount,
            'statDoneSum'   => $doneSum,
            'statDonePaid'  => $donePaid,
            'statDoneDebt'  => $doneDebt,
            'statTotalSum'  => $totalSum,
            'statPaidSum'   => $paidSum,
            'statDebt'      => $debtSum,
            'statPaidPct'   => $paidPct,
            'byServiceType'      => $byServiceType,
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
