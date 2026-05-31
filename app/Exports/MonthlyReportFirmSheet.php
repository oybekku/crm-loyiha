<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MonthlyReportFirmSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    private int   $summaryEndRow  = 0;
    private int   $empHeaderRow   = 0;
    private int   $empSubRow      = 0;
    private int   $empTotalRow    = 0;
    private int   $warnHeaderRow  = 0;
    private int   $warnSubRow     = 0;
    private array $empDataRows    = [];

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

    public function title(): string
    {
        return 'Firma';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 26,
            'C' => 14,
            'D' => 14,
            'E' => 14,
            'F' => 18,
            'G' => 18,
            'H' => 18,
            'I' => 18,
        ];
    }

    public function array(): array
    {
        $rows = [];
        $row  = 1;

        // ── Title ──
        $rows[] = ["FIRMA HISOBOTI — {$this->monthLabel}", '', '', '', '', '', '', ''];
        $row++;

        $rows[] = ['', '', '', '', '', '', '', ''];
        $row++;

        // ── Summary cards ──
        $rows[] = ['Ko\'rsatkich', 'Qiymat', '', '', '', '', '', ''];
        $row++;
        $rows[] = ['To\'langan loyihalar soni',    $this->projectsTotal,    '', '', '', '', '', '', ''];
        $rows[] = ['Xizmatlar jami (so\'m)',        $this->totalServicesSum, '', '', '', '', '', '', ''];
        $rows[] = ['Hodimlar ulushi jami (so\'m)',  $this->totalCommissions, '', '', '', '', '', '', ''];
        $rows[] = ['Avanslar jami (so\'m)',         $this->totalAdvances,    '', '', '', '', '', '', ''];
        $rows[] = ['Firma sof daromadi (so\'m)',    $this->firmIncome,       '', '', '', '', '', '', ''];
        $row += 5;
        $this->summaryEndRow = $row - 1;

        $rows[] = ['', '', '', '', '', '', '', ''];
        $row++;

        // ── Employee summary ──
        $rows[] = ['HODIMLAR XULOSASI', '', '', '', '', '', '', '', ''];
        $this->empHeaderRow = $row;
        $row++;

        $rows[] = ['#', 'Hodim', 'Loyihalar', "O'z vaqtida", 'Kechikkan', 'Xizmatlar (so\'m)', 'Hisoblangan (so\'m)', 'Avans (so\'m)', 'Qolgan to\'lov (so\'m)'];
        $this->empSubRow = $row;
        $row++;

        $i = 1;
        foreach ($this->userStats as $stat) {
            $ontime     = $stat['ontime_count'] ?? 0;
            $late       = $stat['late_count']   ?? 0;
            $sTotal     = $stat['services_total'];
            $comm       = $stat['commission'];
            $advTotal   = $stat['advance_total'] ?? 0;
            $netPayable = $stat['net_payable']   ?? max(0, $comm - $advTotal);

            $rows[] = [
                $i++,
                $stat['user']->name,
                $stat['project_count'],
                $ontime,
                $late,
                $sTotal,
                $comm,
                $advTotal > 0 ? $advTotal : '',
                $netPayable,
            ];
            $this->empDataRows[] = $row;
            $row++;
        }

        // Employee totals
        $totalNet = $this->totalCommissions - $this->totalAdvances;
        $rows[] = [
            '', 'JAMI:', '',
            array_sum(array_column($this->userStats, 'ontime_count')),
            array_sum(array_column($this->userStats, 'late_count')),
            $this->totalServicesSum,
            $this->totalCommissions,
            $this->totalAdvances > 0 ? $this->totalAdvances : '',
            $totalNet,
        ];
        $this->empTotalRow = $row;
        $row++;

        $rows[] = ['', '', '', '', '', '', '', '', ''];
        $row++;

        // ── Warnings (to'liq to'lanmaganlar) ──
        if ($this->warnings->count() > 0) {
            $rows[] = ["DIQQAT: TO'LOV TO'LIQ EMAS ({$this->warnings->count()} ta)", '', '', '', '', '', '', '', ''];
            $this->warnHeaderRow = $row;
            $row++;

            $rows[] = ['#', 'Loyiha №', 'Mijoz', 'Manzil', "Jami (so'm)", "To'langan (so'm)", "Qoldiq (so'm)", '', ''];
            $this->warnSubRow = $row;
            $row++;

            foreach ($this->warnings as $k => $wp) {
                $rows[] = [
                    $k + 1,
                    $wp->number,
                    $wp->owner_name,
                    $wp->address,
                    (float)$wp->total_price,
                    (float)$wp->paid_amount,
                    (float)($wp->total_price - $wp->paid_amount),
                    '', '',
                ];
                $row++;
            }
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $styles = [];

        // Title
        $sheet->mergeCells('A1:I1');
        $styles[1] = [
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '166534']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Summary header row (row 3)
        $styles[3] = [
            'font' => ['bold' => true, 'color' => ['rgb' => '374151']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
        ];
        // Summary data rows 4-7
        for ($r = 4; $r <= $this->summaryEndRow; $r++) {
            $styles[$r] = [
                'font' => ['size' => 11],
            ];
        }
        // Highlight firm income row
        $sheet->getStyle("A{$this->summaryEndRow}:B{$this->summaryEndRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '166534']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCFCE7']],
        ]);

        // Employee section header
        $sheet->mergeCells("A{$this->empHeaderRow}:I{$this->empHeaderRow}");
        $styles[$this->empHeaderRow] = [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1E40AF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ];
        $sheet->getRowDimension($this->empHeaderRow)->setRowHeight(22);

        // Employee sub-header
        $styles[$this->empSubRow] = [
            'font'      => ['bold' => true, 'color' => ['rgb' => '374151']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'CBD5E1']]],
        ];

        // Employee total
        $styles[$this->empTotalRow] = [
            'font' => ['bold' => true, 'color' => ['rgb' => '166534']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0FDF4']],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '86EFAC']]],
        ];

        // Warning header
        if ($this->warnHeaderRow) {
            $sheet->mergeCells("A{$this->warnHeaderRow}:I{$this->warnHeaderRow}");
            $styles[$this->warnHeaderRow] = [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC2626']],
            ];
            $sheet->getRowDimension($this->warnHeaderRow)->setRowHeight(22);
        }

        if ($this->warnSubRow) {
            $styles[$this->warnSubRow] = [
                'font'    => ['bold' => true, 'color' => ['rgb' => '374151']],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF2F2']],
                'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'FCA5A5']]],
            ];
        }

        // Number formats
        $lastRow = max($this->empTotalRow + 20, 50);
        $sheet->getStyle("B4:B{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("F{$this->empSubRow}:I{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        if ($this->warnSubRow) {
            $sheet->getStyle("E{$this->warnSubRow}:G{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        }

        return $styles;
    }
}
