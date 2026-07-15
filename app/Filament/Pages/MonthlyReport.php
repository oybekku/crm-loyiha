<?php

namespace App\Filament\Pages;

use App\Exports\MonthlyReportExport;
use App\Models\EmployeeAdvance;
use App\Models\Project;
use App\Models\ProjectStatusLog;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MonthlyReport extends Page
{
    protected static string  $view           = 'filament.pages.monthly-report';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Oylik hisobot';
    protected static ?string $navigationGroup = 'Sozlamalar';
    protected static ?int    $navigationSort  = 11;
    protected static ?string $title           = 'Oylik hisobot';

    public string $selectedMonth = '';

    // Yillik norma jadvali uchun
    public int   $normYear  = 0;
    public array $normEdits = []; // user_id => norma (tahrirlash)

    // Jarima (user_id => summa)
    public array $penalties   = [];

    // Ish haqi to'lovi modal
    public bool   $showSalaryPayModal = false;
    public int    $salaryPayUserId    = 0;
    public string $salaryPayAmount    = '';
    public string $salaryPayDate      = '';
    public string $salaryPayNote      = '';
    public int    $salaryPayEditId    = 0; // tahrirlash uchun

    // To'liq ma'lumot modal
    public bool   $showDetailModal  = false;
    public int    $detailUserId     = 0;

    public function payServiceShare(int $serviceId, int $userId, float $amount): void
    {
        if (!auth()->user()?->isAdmin()) return;

        $svc = \App\Models\ProjectService::with('project')->findOrFail($serviceId);

        \App\Models\EmployeeSalaryPayment::create([
            'user_id'  => $userId,
            'month'    => $this->selectedMonth,
            'amount'   => $amount,
            'paid_at'  => now()->toDateString(),
            'note'     => 'svc:' . $serviceId . '|' . $svc->project?->number . ' — ' . $svc->service_label . ' ulushi',
            'given_by' => auth()->id(),
        ]);

        Notification::make()->title("To'lov yozildi: " . number_format($amount, 0, '.', ' ') . " so'm")->success()->send();
    }

    public function openDetailModal(int $uid): void
    {
        $this->detailUserId    = $uid;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailUserId    = 0;
    }

    public function openSalaryPayModal(int $userId): void
    {
        $this->salaryPayUserId = $userId;
        $this->salaryPayAmount = '';
        $this->salaryPayDate   = now()->format('Y-m-d');
        $this->salaryPayNote   = '';
        $this->salaryPayEditId = 0;
        $this->showSalaryPayModal = true;
    }

    public function editSalaryPay(int $payId): void
    {
        $pay = \App\Models\EmployeeSalaryPayment::find($payId);
        if (!$pay) return;
        $this->salaryPayUserId = $pay->user_id;
        $this->salaryPayAmount = (string) $pay->amount;
        $this->salaryPayDate   = $pay->paid_at->format('Y-m-d');
        $this->salaryPayNote   = $pay->note ?? '';
        $this->salaryPayEditId = $payId;
        $this->showSalaryPayModal = true;
    }

    public function saveSalaryPay(): void
    {
        $amount = (float) str_replace([' ', ','], '', $this->salaryPayAmount);
        if ($amount <= 0) return;

        $data = [
            'user_id'  => $this->salaryPayUserId,
            'month'    => $this->selectedMonth,
            'amount'   => $amount,
            'paid_at'  => $this->salaryPayDate ?: now()->toDateString(),
            'note'     => trim($this->salaryPayNote) ?: null,
            'given_by' => auth()->id(),
        ];

        if ($this->salaryPayEditId) {
            \App\Models\EmployeeSalaryPayment::find($this->salaryPayEditId)?->update($data);
        } else {
            \App\Models\EmployeeSalaryPayment::create($data);
        }

        $this->showSalaryPayModal = false;
        Notification::make()->title('Saqlandi!')->success()->send();
    }

    public function deleteSalaryPay(int $payId): void
    {
        \App\Models\EmployeeSalaryPayment::find($payId)?->delete();
        Notification::make()->title("O'chirildi!")->warning()->send();
    }

    public function closeSalaryPayModal(): void
    {
        $this->showSalaryPayModal = false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() || auth()->user()?->isMenejer();
    }

    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m');
        $this->normYear      = (int) now()->format('Y');
        $this->normEdits     = User::pluck('monthly_norm', 'id')
            ->map(fn($v) => (int) $v)->toArray();
    }

    public function normYearShift(int $delta): void
    {
        $this->normYear += $delta;
    }

    public function saveNorm(int $uid): void
    {
        if (!auth()->user()?->isAdmin() && !auth()->user()?->isMenejer()) return;

        $val = max(0, (int) ($this->normEdits[$uid] ?? 0));
        User::whereKey($uid)->update(['monthly_norm' => $val]);
        $this->normEdits[$uid] = $val;

        Notification::make()->title('Norma saqlandi')->success()->send();
    }


    public function exportExcel(): BinaryFileResponse
    {
        $data       = $this->getViewData();
        $monthLabel = Carbon::createFromFormat('Y-m', $this->selectedMonth)
            ->translatedFormat('F Y');

        $export = new MonthlyReportExport(
            userStats:        $data['userStats'],
            warnings:         $data['warnings'],
            totalServicesSum: (float) $data['totalServicesSum'],
            totalCommissions: (float) $data['totalCommissions'],
            totalAdvances:    (float) $data['totalAdvances'],
            firmIncome:       (float) $data['firmIncome'],
            projectsTotal:    (int)   $data['projectsTotal'],
            monthLabel:       $monthLabel,
        );

        return Excel::download($export, 'hisobot-' . $this->selectedMonth . '.xlsx');
    }

    public function getViewData(): array
    {
        [$year, $month] = explode('-', $this->selectedMonth);

        // Komissiya HAR BAJARILGAN ish bo'yicha — xizmat AYNAN TUGATILGAN oyiga (completed_at)
        // qarab hisoblanadi (loyiha to'liq arxivga o'tishini yoki qachon ochilganini kutmaydi).
        // Shu sababli eski oyda ochilgan loyihaga shu oy qo'shilib tugatilgan xizmat — shu
        // oy hisobotiga tushadi, eski (allaqachon yopilgan) oy hisobotini o'zgartirmaydi.
        // Bekor qilingan loyiha hisobga olinmaydi.
        $completedServices = \App\Models\ProjectService::with(['assignedUser', 'project.payments'])
            ->whereNotNull('completed_at')
            ->whereNotNull('assigned_user_id')
            ->whereYear('completed_at', $year)
            ->whereMonth('completed_at', $month)
            ->whereHas('project', fn($q) => $q->where('status', '!=', 'bekor_qilingan'))
            ->get();

        $projectIds = $completedServices->pluck('project_id')->unique();

        $projects = Project::with(['services.assignedUser', 'payments'])
            ->whereIn('id', $projectIds)
            ->get();

        // To'landi bosib yozilgan xizmat to'lovlari
        $paidServiceNotes = \App\Models\EmployeeSalaryPayment::where('month', $this->selectedMonth)
            ->whereNotNull('note')
            ->pluck('note')
            ->toArray();

        $advancesByUser = \App\Models\EmployeeSalaryPayment::with('giver')
            ->where('month', $this->selectedMonth)
            ->get()
            ->groupBy('user_id');

        $userStats = [];

        foreach ($completedServices as $service) {
            if (!$service->assignedUser) continue;

            $project = $projects->find($service->project_id);
            if (!$project) continue;
            $paidAt       = $service->completed_at;
            $deadlineDate = $project?->deadline_date
                ? Carbon::parse($project->deadline_date)
                : null;

                $uid  = $service->assigned_user_id;
                $rate = (float) ($service->assignedUser->commission_rate ?? 20);
                if (in_array($service->assignedUser->role, ['admin', 'menejer'])) {
                    $rate = 0;
                }
                $price = (float) $service->final_price;

                $isLate = $deadlineDate && $paidAt
                    && $paidAt->copy()->startOfDay()->gt($deadlineDate->copy()->startOfDay());

                $lateDays = ($isLate && $deadlineDate && $paidAt)
                    ? (int) $deadlineDate->diffInDays($paidAt)
                    : 0;

                if (!isset($userStats[$uid])) {
                    $userStats[$uid] = [
                        'user'           => $service->assignedUser,
                        'project_ids'    => [],
                        'services'       => [],
                        'services_total' => 0.0,
                        'commission'     => 0.0,
                        'late_count'     => 0,
                        'ontime_count'   => 0,
                        'advances'       => $advancesByUser->get($uid, collect()),
                        'advance_total'  => (float) $advancesByUser->get($uid, collect())->sum('amount'),
                    ];
                }

                // Proportional to'langan ulush
                $commission    = round($price * $rate / 100, 2);
                $projTotal     = (float) $project->total_price;
                $projPaid      = (float) $project->paid_amount;
                $paidRatio     = $projTotal > 0 ? min(1, $projPaid / $projTotal) : 0;
                $commPaid      = round($commission * $paidRatio, 0);
                $commRemaining = max(0, $commission - $commPaid);

                $userStats[$uid]['project_ids'][] = $project->id;
                $userStats[$uid]['services'][]    = [
                    'project_id'     => $project->id,
                    'project_number' => $project->number,
                    'owner_name'     => $project->owner_name,
                    'address'        => $project->address,
                    'service_name'   => $service->service_name,
                    'service_label'  => $service->service_label,
                    'price'          => $price,
                    'commission'     => $commission,
                    'comm_paid'      => $commPaid,
                    'comm_remaining' => $commRemaining,
                    'paid_ratio'     => round($paidRatio * 100),
                    'rate'           => $rate,
                    'deadline_date'  => $deadlineDate,
                    'paid_at'        => $paidAt,
                    'is_late'        => $isLate,
                    'late_days'      => $lateDays,
                ];

                $userStats[$uid]['services_total'] += $price;
                $userStats[$uid]['commission']     += round($price * $rate / 100, 2);

                if ($deadlineDate) {
                    $isLate
                        ? $userStats[$uid]['late_count']++
                        : $userStats[$uid]['ontime_count']++;
                }
        }

        // Arxiv bo'lmagan statuslar
        $archiveStatuses = ['tugallangan', 'taqdim_etilgan', 'bekor_qilingan'];

        // Shu oyda ochilgan loyihalarда ishi bor hodimlarni ham qo'shamiz
        $allAssignedUsers = \App\Models\ProjectService::whereNotNull('assigned_user_id')
            ->whereHas('project', fn($q) => $q->whereNotIn('status', $archiveStatuses)
                ->whereYear('created_at', $year)->whereMonth('created_at', $month))
            ->with('assignedUser')
            ->get()
            ->pluck('assignedUser')
            ->filter()
            ->unique('id');

        foreach ($allAssignedUsers as $u) {
            if (!isset($userStats[$u->id])) {
                $userStats[$u->id] = [
                    'user'           => $u,
                    'project_ids'    => [],
                    'services'       => [],
                    'services_total' => 0.0,
                    'commission'     => 0.0,
                    'late_count'     => 0,
                    'ontime_count'   => 0,
                    'advances'       => collect(),
                    'advance_total'  => 0.0,
                ];
            }
        }

        foreach ($userStats as $uid => &$stat) {
            $stat['project_count'] = count(array_unique($stat['project_ids']));

            $penalty   = (float) ($this->penalties[$uid] ?? 0);

            // Ish haqi to'lovlari (DB dan)
            $salaryPays = \App\Models\EmployeeSalaryPayment::where('user_id', $uid)
                ->where('month', $this->selectedMonth)
                ->orderBy('paid_at')
                ->get();
            $paidTotal = (float) $salaryPays->sum('amount');

            $stat['penalty']      = $penalty;
            $stat['salary_pays']  = $salaryPays;
            $stat['paid_total']   = $paidTotal;
            $stat['net_payable']  = max(0, $stat['commission'] - $stat['advance_total'] - $penalty);

            // Kutayotgan ishlar (shu oyda ochilgan, tugatilmagan / to'lanmagan)
            $pendingServices = \App\Models\ProjectService::where('assigned_user_id', $uid)
                ->whereHas('project', fn($q) => $q->whereNotIn('status', $archiveStatuses)
                    ->whereYear('created_at', $year)->whereMonth('created_at', $month))
                ->with('project:id,number,owner_name,status')
                ->get();

            $stat['pending_count'] = $pendingServices->count();
            $stat['pending_sum']   = (float) $pendingServices->sum('final_price');
            $stat['pending_items'] = $pendingServices->map(function($s) use ($paidServiceNotes) {
                $daysLeft = null;
                $isLate   = false;
                $lateDays = 0;
                if ($s->work_started_at && $s->deadline_days) {
                    $deadline = \Carbon\Carbon::parse($s->work_started_at)->addDays((int)$s->deadline_days);
                    $diff = (int) now()->diffInDays($deadline, false);
                    $isLate   = $diff < 0;
                    $daysLeft = $diff;
                    $lateDays = $isLate ? abs($diff) : 0;
                }
                $rate = (float) ($s->assignedUser->commission_rate ?? 20);
                if (in_array($s->assignedUser?->role, ['admin', 'menejer'])) $rate = 0;
                $myShare = round((float)$s->final_price * $rate / 100, 0);
                $isPaid = collect($paidServiceNotes)->contains(fn($n) => str_starts_with($n, 'svc:' . $s->id . '|'));
                return [
                    'service_id'     => $s->id,
                    'user_id'        => $s->assigned_user_id,
                    'is_paid'        => $isPaid,
                    'project_number' => $s->project?->number,
                    'owner_name'     => $s->project?->owner_name,
                    'service_label'  => $s->service_label,
                    'price'          => (float)$s->final_price,
                    'my_share'       => $myShare,
                    'rate'           => $rate,
                    'status'         => $s->project?->status,
                    'days_left'      => $daysLeft,
                    'is_late'        => $isLate,
                    'late_days'      => $lateDays,
                    'deadline_days'  => $s->deadline_days,
                    'work_started'   => $s->work_started_at,
                ];
            })->toArray();
        }
        unset($stat);

        $warnings = $projects->filter(
            fn($p) => $p->paid_amount < $p->total_price && $p->total_price > 0
        );

        $totalServicesSum  = array_sum(array_column($userStats, 'services_total'));
        $totalCommissions  = array_sum(array_column($userStats, 'commission'));
        $totalAdvances     = array_sum(array_column($userStats, 'advance_total'));
        $firmIncome        = $totalServicesSum - $totalCommissions;
        $projectsTotal     = $projects->count();

        // Tugatilgan ishni 2 ga ajratish (ikkalasi ham komissiyaga kiradi — faqat ko'rsatish uchun):
        //  - To'liq: arxivga o'tgan (tugallangan/taqdim etilgan) loyihalardagilar
        //  - Qisman: hali faol loyihalardagi tugatilgan ishlar
        $toliqTugatilgan = 0.0; $qismanTugatilgan = 0.0;
        $toliqIds = []; $qismanIds = [];
        foreach ($completedServices as $cs) {
            $st = $cs->project?->status;
            if (in_array($st, ['tugallangan', 'taqdim_etilgan'])) {
                $toliqTugatilgan += (float) $cs->final_price;
                $toliqIds[$cs->project_id] = true;
            } else {
                $qismanTugatilgan += (float) $cs->final_price;
                $qismanIds[$cs->project_id] = true;
            }
        }
        $toliqCount  = count($toliqIds);
        $qismanCount = count($qismanIds);

        // Tugatilgan ishlar ro'yxati — shu oy TUGATILGAN barcha xizmatlar (loyiha qaysi oyda
        // ochilganidan qat'i nazar), bekor qilingandan tashqari. Arxiv = daromadga kirgan,
        // Jarayonda = faol loyiha.
        $statusLabels = \App\Models\ProjectStatus::pluck('label', 'key')->toArray();
        $tugatilganIshlar = \App\Models\ProjectService::with(['assignedUser', 'project:id,number,owner_name,status'])
            ->whereNotNull('completed_at')
            ->whereNotNull('assigned_user_id')
            ->whereYear('completed_at', $year)
            ->whereMonth('completed_at', $month)
            ->whereHas('project', fn($q) => $q->where('status', '!=', 'bekor_qilingan'))
            ->orderByDesc('completed_at')
            ->get()
            ->map(function ($s) use ($statusLabels) {
                $rate = (float) ($s->assignedUser->commission_rate ?? 20);
                if (in_array($s->assignedUser?->role, ['admin', 'menejer'])) $rate = 0;
                $st = $s->project?->status;
                return [
                    'project_id'   => $s->project_id,
                    'number'       => $s->project?->number,
                    'owner'        => $s->project?->owner_name,
                    'service'      => \App\Models\Project::serviceOptions()[$s->service_name] ?? $s->service_name,
                    'employee'     => $s->assignedUser?->name ?? '—',
                    'price'        => (float) $s->final_price,
                    'commission'   => round((float) $s->final_price * $rate / 100),
                    'date'         => $s->completed_at,
                    'is_arxiv'     => in_array($st, ['tugallangan', 'taqdim_etilgan']),
                    'status_label' => $statusLabels[$st] ?? $st,
                ];
            });

        // Qilinmagan ishlar — shu oyda OCHILGAN, arxivda emas, hali tugallanmagan
        $pendingQuery         = Project::whereNotIn('status', $archiveStatuses)
            ->whereYear('created_at', $year)->whereMonth('created_at', $month);
        $pendingProjectsSum   = (float) (clone $pendingQuery)->sum('total_price');
        $pendingProjectsPaid  = (float) (clone $pendingQuery)->sum('paid_amount');
        $pendingProjectsDebt  = $pendingProjectsSum - $pendingProjectsPaid;
        $pendingProjectsPct   = $pendingProjectsSum > 0 ? round($pendingProjectsPaid / $pendingProjectsSum * 100) : 0;
        $pendingProjectsCount = (clone $pendingQuery)->count();

        // Taxminiy hodimlar ulushi (shu oyda ochilgan faol loyihalar bo'yicha)
        $pendingServices = \App\Models\ProjectService::with('assignedUser')
            ->whereHas('project', fn($q) => $q->whereNotIn('status', $archiveStatuses)
                ->whereYear('created_at', $year)->whereMonth('created_at', $month))
            ->whereNotNull('assigned_user_id')
            ->get();

        // Bu oy berilgan to'lovlar (user_id => summa)
        $advancesThisMonth = \App\Models\EmployeeSalaryPayment::where('month', $this->selectedMonth)
            ->get()
            ->groupBy('user_id')
            ->map(fn($g) => (float) $g->sum('amount'));

        $pendingWorkersShare = 0.0;
        $pendingWorkerStats  = [];
        foreach ($pendingServices as $ps) {
            if (!$ps->assignedUser) continue;
            $r = (float)($ps->assignedUser->commission_rate ?? 20);
            if (in_array($ps->assignedUser->role, ['admin', 'menejer'])) $r = 0;
            $share = round((float)$ps->final_price * $r / 100);
            $pendingWorkersShare += $share;
            $uid = $ps->assigned_user_id;
            if (!isset($pendingWorkerStats[$uid])) {
                $given = $advancesThisMonth->get($uid, 0);
                $pendingWorkerStats[$uid] = [
                    'name'    => $ps->assignedUser->name,
                    'share'   => 0,
                    'given'   => $given,
                ];
            }
            $pendingWorkerStats[$uid]['share'] += $share;
        }
        // remaining hisoblash
        foreach ($pendingWorkerStats as &$ws) {
            $ws['remaining'] = max(0, $ws['share'] - $ws['given']);
        }
        unset($ws);
        arsort($pendingWorkerStats);
        $pendingFirmaShare = $pendingProjectsSum - $pendingWorkersShare;

        $allUsers = User::orderBy('name')->get();

        // ══ YILLIK NORMA JADVALI ══
        // Har hodim, tanlangan yilning 12 oyi bo'yicha BAJARILGAN (completed_at) ish soni.
        $normYear = $this->normYear ?: (int) now()->format('Y');
        $yearCompleted = \App\Models\ProjectService::whereNotNull('completed_at')
            ->whereNotNull('assigned_user_id')
            ->whereYear('completed_at', $normYear)
            ->whereHas('project', fn($q) => $q->where('status', '!=', 'bekor_qilingan'))
            ->get(['assigned_user_id', 'completed_at']);

        $normCounts = []; // [uid][oy] => son
        foreach ($yearCompleted as $s) {
            $m = (int) Carbon::parse($s->completed_at)->format('n');
            $normCounts[$s->assigned_user_id][$m] = ($normCounts[$s->assigned_user_id][$m] ?? 0) + 1;
        }

        $normRows = [];
        foreach ($allUsers as $u) {
            // Faqat ish bajaradigan hodimlar. Admin/menejer/hisobchi ish qilmaydi — chiqarib tashlanadi.
            if (in_array($u->role, ['admin', 'menejer', 'hisobchi'])) continue;
            $norm     = (int) ($u->monthly_norm ?? 0);
            $months   = [];
            $metCount = 0;
            $yearTotal = 0;
            for ($m = 1; $m <= 12; $m++) {
                $cnt = (int) ($normCounts[$u->id][$m] ?? 0);
                $yearTotal += $cnt;
                // met: true=bajarilgan(yashil), false=bajarilmagan(qizil), null=norma belgilanmagan
                $met = $norm > 0 ? ($cnt >= $norm) : null;
                if ($met === true) $metCount++;
                $months[$m] = ['count' => $cnt, 'met' => $met];
            }
            $normRows[] = [
                'user'       => $u,
                'norm'       => $norm,
                'months'     => $months,
                'met_count'  => $metCount,
                'year_total' => $yearTotal,
            ];
        }

        // ══ MyGOV — kim orqali kelgan (FISH bo'yicha), loyiha ochilgan oyга ══
        $mygovData = \App\Models\Project::whereNotNull('mygov_fish')
            ->where('mygov_fish', '!=', '')
            ->whereYear('created_at', $normYear)
            ->get(['mygov_fish', 'created_at']);
        $mygovCounts = []; // [fish][oy] => son
        foreach ($mygovData as $mp) {
            $fish = trim($mp->mygov_fish);
            if ($fish === '') continue;
            $m = (int) Carbon::parse($mp->created_at)->format('n');
            $mygovCounts[$fish][$m] = ($mygovCounts[$fish][$m] ?? 0) + 1;
        }
        $mygovRows = [];
        foreach ($mygovCounts as $fish => $months) {
            $full = [];
            $total = 0;
            for ($m = 1; $m <= 12; $m++) { $c = (int) ($months[$m] ?? 0); $full[$m] = $c; $total += $c; }
            $mygovRows[] = ['fish' => $fish, 'months' => $full, 'total' => $total];
        }
        usort($mygovRows, fn($a, $b) => $b['total'] <=> $a['total']);


        // Umumiy loyihalar — shu oyda ochilgan barcha loyihalar
        $allProjectsCount = (int)   Project::whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
        $allProjectsSum   = (float) Project::whereYear('created_at', $year)->whereMonth('created_at', $month)->sum('total_price');

        return compact(
            'userStats', 'warnings', 'projects',
            'totalServicesSum', 'totalCommissions', 'totalAdvances',
            'firmIncome', 'projectsTotal', 'allUsers',
            'pendingProjectsSum', 'pendingProjectsCount',
            'pendingProjectsPaid', 'pendingProjectsDebt', 'pendingProjectsPct',
            'pendingWorkersShare', 'pendingFirmaShare', 'pendingWorkerStats',
            'allProjectsCount', 'allProjectsSum',
            'toliqTugatilgan', 'qismanTugatilgan', 'toliqCount', 'qismanCount',
            'tugatilganIshlar',
            'normRows', 'normYear', 'mygovRows'
        );
    }
}
