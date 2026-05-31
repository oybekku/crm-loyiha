<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MonthlyReportExport implements WithMultipleSheets
{
    public function __construct(
        private array  $userStats,
        private object $warnings,
        private float  $totalServicesSum,
        private float  $totalCommissions,
        private float  $totalAdvances,
        private float  $firmIncome,
        private int    $projectsTotal,
        private string $monthLabel,
    ) {}

    public function sheets(): array
    {
        return [
            new MonthlyReportEmployeesSheet(
                $this->userStats,
                $this->monthLabel,
            ),
            new MonthlyReportFirmSheet(
                $this->userStats,
                $this->warnings,
                $this->totalServicesSum,
                $this->totalCommissions,
                $this->totalAdvances,
                $this->firmIncome,
                $this->projectsTotal,
                $this->monthLabel,
            ),
        ];
    }
}
