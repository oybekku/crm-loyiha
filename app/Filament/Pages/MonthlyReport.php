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
            'note'     => $svc->project?->number . ' — ' . $svc->service_label . ' ulushi',
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

        // Bu oy completed_at bo'lgan xizmatlar asosida hisobot
        $completedServices = \App\Models\ProjectService::with(['assignedUser', 'project.payments'])
            ->whereNotNull('completed_at')
            ->whereYear('completed_at', $year)
            ->whereMonth('completed_at', $month)
            ->whereNotNull('assigned_user_id')
            ->get();

        $projectIds = $completedServices->pluck('project_id')->unique();

        $projects = Project::with(['services.assignedUser', 'payments'])
            ->whereIn('id', $projectIds)
            ->get();

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

        // Kutayotgan ishlari bor lekin bu oyda tugallamagan hodimlarni ham qo'shamiz
        $allAssignedUsers = \App\Models\ProjectService::whereNotNull('assigned_user_id')
            ->whereHas('project', fn($q) => $q->whereNotIn('status', $archiveStatuses))
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

            // Kutayotgan ishlar (tugatilmagan / to'lanmagan)
            $pendingServices = \App\Models\ProjectService::where('assigned_user_id', $uid)
                ->whereHas('project', fn($q) => $q->whereNotIn('status', $archiveStatuses))
                ->with('project:id,number,owner_name,status')
                ->get();

            $stat['pending_count'] = $pendingServices->count();
            $stat['pending_sum']   = (float) $pendingServices->sum('final_price');
            $stat['pending_items'] = $pendingServices->map(function($s) {
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
                return [
                    'service_id'     => $s->id,
                    'user_id'        => $s->assigned_user_id,
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

        // Qilinmagan ishlar (arxivda emas, hali tugallanmagan)
        $pendingQuery         = Project::whereNotIn('status', $archiveStatuses);
        $pendingProjectsSum   = (float) (clone $pendingQuery)->sum('total_price');
        $pendingProjectsPaid  = (float) (clone $pendingQuery)->sum('paid_amount');
        $pendingProjectsDebt  = $pendingProjectsSum - $pendingProjectsPaid;
        $pendingProjectsPct   = $pendingProjectsSum > 0 ? round($pendingProjectsPaid / $pendingProjectsSum * 100) : 0;
        $pendingProjectsCount = (clone $pendingQuery)->count();

        // Taxminiy hodimlar ulushi (faol loyihalar bo'yicha)
        $pendingServices = \App\Models\ProjectService::with('assignedUser')
            ->whereHas('project', fn($q) => $q->whereNotIn('status', $archiveStatuses))
            ->whereNotNull('assigned_user_id')
            ->get();

        // Bu oy berilgan avanslar (user_id => summa)
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

        return compact(
            'userStats', 'warnings', 'projects',
            'totalServicesSum', 'totalCommissions', 'totalAdvances',
            'firmIncome', 'projectsTotal', 'allUsers',
            'pendingProjectsSum', 'pendingProjectsCount',
            'pendingProjectsPaid', 'pendingProjectsDebt', 'pendingProjectsPct',
            'pendingWorkersShare', 'pendingFirmaShare', 'pendingWorkerStats'
        );
    }
}
