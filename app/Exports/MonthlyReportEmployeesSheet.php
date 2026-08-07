<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * WithStrictNullComparison — PhpSpreadsheet'ning fromArray() usulida
 * standart holatda 0 (raqam) qiymati null bilan bir xil (==) solishtirilib,
 * katak bo'sh qoldirilar edi (masalan "To'lanishi kerak: 0 so'm" butunlay
 * yo'qolib qolardi). Bu interfeys qat'iy (===) solishtirishga o'tkazadi.
 */
class MonthlyReportEmployeesSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithStrictNullComparison
{
    private array $rows = [];
    private array $employeeHeaderRows = [];
    private array $subheaderRows = [];
    private array $totalRows = [];
    private int   $currentRow = 1;

    public function __construct(
        private array  $userStats,
        private string $monthLabel,
    ) {}

    public function title(): string
    {
        return 'Hodimlar';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 12,
            'C' => 22,
            'D' => 24,
            'E' => 18,
            'F' => 14,
            'G' => 8,
            'H' => 14,
            'I' => 14,
            'J' => 13,
            'K' => 13,
            'L' => 16,
            'M' => 16,
            'N' => 16,
            'O' => 16,
        ];
    }

    public function array(): array
    {
        // Title row
        $this->rows[] = ["OYLIK HISOBOT — {$this->monthLabel}", '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $this->employeeHeaderRows[] = $this->currentRow;
        $this->currentRow++;

        $this->rows[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $this->currentRow++;

        foreach ($this->userStats as $stat) {
            $user = $stat['user'];
            // Har bir xizmat o'ziga tegishli oy uchun to'g'ri foizni allaqachon
            // saqlagan (EmployeePayableService::commissionForService orqali) —
            // shu bilan Excel ham sahifadagi bilan bir xil raqamni chiqaradi.
            $rate = $stat['services'][0]['rate'] ?? (float)($user->commission_rate ?? 20);
            $role = $user->role_name ?? ucfirst($user->role ?? '');

            $advTotal          = (float)($stat['advance_total'] ?? 0);
            $overpaid          = (float)($stat['overpaid'] ?? 0);
            // "To'lanishi kerak" — Oylik hisobotdagi "To'liq ma'lumot" oynasi
            // bilan AYNAN BIR XIL manba (allaqachon to'langani ayirilgan holda),
            // eski "net_payable" (faqat komissiya − avans) bilan aralashtirilmasin.
            $payableRemaining  = (float)($stat['payable_remaining'] ?? 0);

            // Employee header
            $this->rows[] = [
                "Hodim: {$user->name}",
                "Lavozim: {$role}",
                "Ulush: {$rate}%",
                '', '', '', '', '', '', '', '', '', '', '', '',
            ];
            $this->employeeHeaderRows[] = $this->currentRow;
            $this->currentRow++;

            // Column headers
            $this->rows[] = [
                '#',
                'Loyiha №',
                'Mijoz',
                'Manzil',
                'Xizmat turi',
                'Narx (so\'m)',
                'Foiz',
                'Ulush (so\'m)',
                'To\'landi (so\'m)',
                'Muddat',
                'Tugatilgan',
                'Holat',
                'Avans olingan',
                'Ortiqcha to\'langan',
                'To\'lanishi kerak',
            ];
            $this->subheaderRows[] = $this->currentRow;
            $this->currentRow++;

            // Service rows
            $serviceTotal  = 0.0;
            $commTotal     = 0.0;
            $commPaidTotal = 0.0;

            foreach ($stat['services'] as $j => $srv) {
                $deadline = $srv['deadline_date'] ? $srv['deadline_date']->format('d.m.Y') : '—';
                $paidAt   = $srv['paid_at']       ? $srv['paid_at']->format('d.m.Y')       : '—';

                if (!$srv['deadline_date']) {
                    $holat = 'Muddat yo\'q';
                } elseif ($srv['is_late']) {
                    $holat = "Kechikkan {$srv['late_days']} kun";
                } else {
                    $holat = "O'z vaqtida";
                }

                $this->rows[] = [
                    $j + 1,
                    $srv['project_number'],
                    $srv['owner_name'],
                    $srv['address'] ?? '—',
                    $srv['service_label'] ?? $srv['service_name'] ?? '—',
                    (float)$srv['price'],
                    ($srv['rate'] ?? $rate) > 0 ? "{$srv['rate']}%" : '—',
                    (float)$srv['commission'],
                    // Har bir loyiha bo'yicha ALOHIDA to'langan ulush — sahifadagi
                    // "To'liq ma'lumot" oynasidagi har qatordagi "To'landi" (yashil)
                    // ustuni bilan AYNAN BIR XIL (mijoz to'lagan ulushga mutanosib).
                    (float)($srv['comm_paid'] ?? 0),
                    $deadline,
                    $paidAt,
                    $holat,
                    '', // avans — faqat total qatorida
                    '', // ortiqcha to'langan — faqat total qatorida
                    '', // to'lanishi kerak — faqat total qatorida
                ];
                $serviceTotal  += (float)$srv['price'];
                $commTotal     += (float)$srv['commission'];
                $commPaidTotal += (float)($srv['comm_paid'] ?? 0);
                $this->currentRow++;
            }

            // Employee total row
            $this->rows[] = [
                '', '', '', 'JAMI:', '',
                $serviceTotal,
                '',
                $commTotal,
                $commPaidTotal,
                '', '',
                'Avans: ' . ($advTotal > 0 ? number_format($advTotal, 0, '.', ' ') . " so'm" : '—'),
                $advTotal > 0 ? $advTotal : '',
                $overpaid > 0 ? $overpaid : '',
                $payableRemaining,
            ];
            $this->totalRows[] = $this->currentRow;
            $this->currentRow++;

            // Empty separator row
            $this->rows[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
            $this->currentRow++;
        }

        return $this->rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $styles = [];

        // Title row
        $sheet->mergeCells('A1:O1');
        $styles[1] = [
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getRowDimension(1)->setRowHeight(28);

        foreach ($this->employeeHeaderRows as $row) {
            if ($row === 1) continue;
            $sheet->mergeCells("A{$row}:O{$row}");
            $styles[$row] = [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1E40AF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ];
            $sheet->getRowDimension($row)->setRowHeight(22);
        }

        foreach ($this->subheaderRows as $row) {
            $styles[$row] = [
                'font'      => ['bold' => true, 'color' => ['rgb' => '374151']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => false],
                'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'CBD5E1']]],
            ];
        }

        foreach ($this->totalRows as $row) {
            $styles[$row] = [
                'font' => ['bold' => true, 'color' => ['rgb' => '7C3AED']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F3FF']],
                'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'DDD6FE']]],
            ];
        }

        // Number format for price columns
        $lastRow = $this->currentRow - 1;
        $sheet->getStyle("F3:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("H3:I{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("M3:O{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');

        return $styles;
    }
}
