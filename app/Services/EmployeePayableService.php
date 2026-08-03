<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectService;
use Illuminate\Support\Collection;

/**
 * Xodimning komissiyasi (hodim ulushi) hisob-kitobi — Oylik hisobot va
 * Buxgalteriya sahifalarida bir xil natija chiqishi uchun yagona manba.
 * Buxgalteriyadagi "Xodim ulushi" xarajat qatori shu servisdan olinadi,
 * shuning uchun Oylik hisobotdagi "To'lanishi kerak" o'zgarsa, u yerda ham
 * avtomatik yangilanadi (sinxronlash kerak emas — ikkalasi ham jonli hisoblanadi).
 */
class EmployeePayableService
{
    public static function commissionForService(ProjectService $service, ?Project $project): array
    {
        $user = $service->assignedUser;
        $rate = (float) ($user?->commission_rate ?? 20);
        if ($user && in_array($user->role, ['admin', 'menejer'])) {
            $rate = 0;
        }

        $price      = (float) $service->final_price;
        $commission = round($price * $rate / 100, 2);

        $projTotal = (float) ($project->total_price ?? 0);
        $projPaid  = (float) ($project->paid_amount ?? 0);
        $paidRatio = $projTotal > 0 ? min(1, $projPaid / $projTotal) : 0;

        $commPaid      = round($commission * $paidRatio, 0);
        $commRemaining = max(0, $commission - $commPaid);

        return [
            'rate'           => $rate,
            'price'          => $price,
            'commission'     => $commission,
            'paid_ratio'     => $paidRatio,
            'comm_paid'      => $commPaid,
            'comm_remaining' => $commRemaining,
        ];
    }

    /**
     * Tanlangan oy ('Y-m') uchun har bir hodimning "To'lanishi kerak" summasi
     * — Oylik hisobot detalidagi bilan bir xil formula (mijoz to'lagan ulushga
     * mutanosib ochilgan komissiya yig'indisi, faqat tugatilgan ishlar bo'yicha).
     *
     * @return Collection<int, array{user: \App\Models\User, amount: float}>
     */
    public static function forMonth(string $month): Collection
    {
        [$year, $mon] = explode('-', $month);

        $completedServices = ProjectService::with(['assignedUser', 'project'])
            ->whereNotNull('completed_at')
            ->whereNotNull('assigned_user_id')
            ->whereHas('project', fn ($q) => $q->whereYear('created_at', $year)
                ->whereMonth('created_at', $mon)
                ->where('status', '!=', 'bekor_qilingan'))
            ->get();

        $payable = [];

        foreach ($completedServices as $service) {
            $user    = $service->assignedUser;
            $project = $service->project;
            if (!$user || !$project) continue;

            $calc = self::commissionForService($service, $project);

            if (!isset($payable[$user->id])) {
                $payable[$user->id] = ['user' => $user, 'amount' => 0.0];
            }
            $payable[$user->id]['amount'] += $calc['comm_paid'];
        }

        return collect($payable)
            ->filter(fn ($p) => $p['amount'] > 0)
            ->sortBy(fn ($p) => $p['user']->name)
            ->values();
    }
}
