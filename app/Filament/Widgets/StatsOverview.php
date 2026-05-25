<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $total     = Project::count();
        $yangi     = Project::where('status', 'yangi')->count();
        $jarayon   = Project::whereIn('status', ['tolov_jarayonida', 'tekshirish'])->count();
        $tugallangan = Project::where('status', 'tugallangan')->count();
        $totalSum  = Project::sum('total_price');
        $paidSum   = Project::sum('paid_amount');
        $debt      = $totalSum - $paidSum;

        return [
            Stat::make('Jami loyihalar', $total)
                ->description("Yangi: $yangi | Jarayonda: $jarayon | Tugallangan: $tugallangan")
                ->icon('heroicon-o-folder-open')
                ->color('primary'),

            Stat::make("Jami summa", number_format($totalSum, 0, '.', ' ') . " so'm")
                ->description("To'langan: " . number_format($paidSum, 0, '.', ' ') . " so'm")
                ->icon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make("Qolgan qarz", number_format($debt, 0, '.', ' ') . " so'm")
                ->description($total > 0 ? round(($paidSum / max($totalSum, 1)) * 100) . "% to'langan" : "Ma'lumot yo'q")
                ->icon('heroicon-o-exclamation-circle')
                ->color($debt > 0 ? 'warning' : 'success'),
        ];
    }
}
