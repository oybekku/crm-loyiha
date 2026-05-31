<?php

namespace App\Filament\Pages;

use App\Exports\MonthlyReportExport;
use App\Models\EmployeeAdvance;
use App\Models\Project;
use App\Models\ProjectStatusLog;
use App\Models\User;
use Carbon\Carbon;
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

    // To'liq ma'lumot modal
    public bool   $showDetailModal  = false;
    public int    $detailUserId     = 0;

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

    // Avans modal state
    public bool   $showAdvanceModal = false;
    public int    $advanceUserId    = 0;
    public string $advanceUserName  = '';
    public string $advanceAmount    = '';
    public string $advanceNote      = '';

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() || auth()->user()?->isMenejer();
    }

    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m');
    }

    public function openAdvanceModal(int $userId, string $userName): void
    {
        $this->advanceUserId   = $userId;
        $this->advanceUserName = $userName;
        $this->advanceAmount   = '';
        $this->advanceNote     = '';
        $this->showAdvanceModal = true;
    }

    public function closeAdvanceModal(): void
    {
        $this->showAdvanceModal = false;
        $this->advanceUserId    = 0;
        $this->advanceUserName  = '';
        $this->advanceAmount    = '';
        $this->advanceNote      = '';
    }

    public function saveAdvance(): void
    {
        $amount = (float) str_replace([' ', ','], ['', '.'], $this->advanceAmount);

        if ($amount <= 0 || !$this->advanceUserId) {
            return;
        }

        EmployeeAdvance::create([
            'user_id'  => $this->advanceUserId,
            'given_by' => auth()->id(),
            'amount'   => $amount,
            'month'    => $this->selectedMonth,
            'note'     => trim($this->advanceNote) ?: null,
        ]);

        $this->closeAdvanceModal();
    }

    public function deleteAdvance(int $advanceId): void
    {
        EmployeeAdvance::where('id', $advanceId)
            ->whereHas('user') // safety check
            ->delete();
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

        $tolanganLogs = ProjectStatusLog::where('status', 'tolangan')
            ->whereYear('entered_at', $year)
            ->whereMonth('entered_at', $month)
            ->get();

        $projectIds = $tolanganLogs->pluck('project_id')->unique();

        $tolanganDates = $tolanganLogs
            ->sortByDesc('entered_at')
            ->keyBy('project_id')
            ->map(fn($l) => $l->entered_at);

        $projects = Project::with(['services.assignedUser', 'payments'])
            ->whereIn('id', $projectIds)
            ->get();

        // Avanslar: bu oy berilganlar, user_id bo'yicha guruh
        $advancesByUser = EmployeeAdvance::with('giver')
            ->where('month', $this->selectedMonth)
            ->get()
            ->groupBy('user_id');

        $userStats = [];

        foreach ($projects as $project) {
            $paidAt       = $tolanganDates->get($project->id);
            $deadlineDate = $project->deadline_date
                ? Carbon::parse($project->deadline_date)
                : null;

            foreach ($project->services as $service) {
                if (!$service->assigned_user_id || !$service->assignedUser) continue;

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

                $userStats[$uid]['project_ids'][] = $project->id;
                $userStats[$uid]['services'][]    = [
                    'project_id'     => $project->id,
                    'project_number' => $project->number,
                    'owner_name'     => $project->owner_name,
                    'address'        => $project->address,
                    'service_name'   => $service->service_name,
                    'service_label'  => $service->service_label,
                    'price'          => $price,
                    'commission'     => round($price * $rate / 100, 2),
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
        }

        foreach ($userStats as &$stat) {
            $stat['project_count'] = count(array_unique($stat['project_ids']));
            $stat['net_payable']   = max(0, $stat['commission'] - $stat['advance_total']);
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

        $allUsers = User::orderBy('name')->get();

        return compact(
            'userStats', 'warnings', 'projects',
            'totalServicesSum', 'totalCommissions', 'totalAdvances',
            'firmIncome', 'projectsTotal', 'allUsers'
        );
    }
}
