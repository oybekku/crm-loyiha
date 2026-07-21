<?php

namespace App\Services;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectService;
use App\Models\EmployeeSalaryPayment;
use App\Models\EmployeeAdvance;

/**
 * Xodim "Mening balansim" oynasi uchun hisob-kitob.
 *
 * Qoidalar (kelishilgan):
 *  - Bir ish komissiyasi = ish ASL narxi (price, chegirmasiz) × xodim foizi (commission_rate,
 *    default 20%) — MINUS shu ishga qo'yilgan chegirma summasi (price − final_price).
 *    Ya'ni chegirma TO'LIQ xodim komissiyasidan ayiriladi — firma ulushi chegirmadan
 *    ta'sirlanmaydi (har doim asl narxdan hisoblangan ulushini oladi). Agar chegirma
 *    komissiyadan katta bo'lsa, komissiya manfiy chiqishi mumkin (xodim shu ishda "qarzga kiradi").
 *    Admin/menejer komissiya olmaydi (0%).
 *  - "Tasdiqlangan kirim" = ish TUGATILGAN (completed_at) VA shu ishga (chegirmadan keyingi
 *    narxga, ya'ni final_price'ga) to'liq TO'LANGAN bo'lsa.
 *  - "Jarayonda" = qolgan (tugatilmagan yoki to'lanmagan) ishlar komissiyasi.
 *  - "Chiqim" = xodim olgan oylik (EmployeeSalaryPayment) + avanslar (EmployeeAdvance).
 *  - "Balans / firma qarzi" = tasdiqlangan kirim − chiqim.
 */
class BalanceService
{
    /**
     * $year/$month berilsa — faqat o'sha oyda OCHILGAN loyihalar (va o'sha
     * oyga tegishli oylik/avans yozuvlari) hisoblanadi. Bu Oylik hisobot
     * (FirmReportService) bilan bir xil "loyiha ochilgan oyi" mantig'i —
     * shu bilan ikkala joydagi summalar mos keladi, chalkashlik bo'lmaydi.
     * Berilmasa (null) — butun davr (eski xatti-harakat).
     */
    public static function forUser(int $userId, ?int $year = null, ?int $month = null): array
    {
        $user = User::find($userId);
        if (!$user) {
            return self::empty();
        }

        $rate = (float) ($user->commission_rate ?? 20);
        if (in_array($user->role, ['admin', 'menejer'])) {
            $rate = 0;
        }

        $monthStr = ($year && $month) ? sprintf('%04d-%02d', $year, $month) : null;

        $earned  = 0.0;   // tasdiqlangan kirim (tugatilgan + to'langan)
        $pending = 0.0;   // jarayonda
        $txns    = [];

        if ($rate > 0) {
            $services = ProjectService::with(['project.services', 'project.payments'])
                ->where('assigned_user_id', $userId)
                ->whereHas('project', function ($q) use ($year, $month) {
                    $q->where('status', '!=', 'bekor_qilingan');
                    if ($year && $month) {
                        $q->whereYear('created_at', $year)->whereMonth('created_at', $month);
                    }
                })
                ->get();

            foreach ($services as $s) {
                $origPrice  = (float) $s->price;
                $finalPrice = (float) $s->final_price;
                if ($origPrice <= 0) continue;

                // Chegirma bo'lsa — to'liq shu yerda xodim komissiyasidan ayiriladi.
                $discountAmount = max(0, $origPrice - $finalPrice);
                $commission     = round($origPrice * $rate / 100, 0) - $discountAmount;
                if ($commission == 0) continue;

                $paidForService = self::paidForService($s);
                $isCompleted    = (bool) $s->completed_at;
                $isPaid         = $paidForService >= $finalPrice - 0.01;
                $confirmed      = $isCompleted && $isPaid;

                if ($confirmed) {
                    $earned += $commission;
                } else {
                    $pending += $commission;
                }

                $txns[] = [
                    'type'    => 'ish',
                    'dir'     => $commission >= 0 ? 'in' : 'out',
                    'date'    => ($s->completed_at ?? $s->created_at),
                    'owner'   => $s->project?->owner_name ?? '—',
                    'number'  => $s->project?->number ?? '',
                    'service' => Project::serviceOptions()[$s->service_name] ?? $s->service_name,
                    'amount'  => abs($commission),
                    'status'  => $confirmed ? 'tasdiqlangan' : 'jarayonda',
                ];
            }
        }

        // ── Chiqim: oylik to'lovlar va avanslar (o'zining "month" maydoni bo'yicha) ──
        $salariesQ = EmployeeSalaryPayment::where('user_id', $userId);
        $advancesQ = EmployeeAdvance::where('user_id', $userId);
        if ($monthStr) {
            $salariesQ->where('month', $monthStr);
            $advancesQ->where('month', $monthStr);
        }
        $salaries = $salariesQ->get();
        $advances = $advancesQ->get();

        $withdrawn = 0.0;
        foreach ($salaries as $p) {
            $withdrawn += (float) $p->amount;
            $txns[] = [
                'type'    => 'oylik',
                'dir'     => 'out',
                'date'    => $p->paid_at,
                'owner'   => 'Oylik to\'lov',
                'number'  => $p->month ?? '',
                'service' => 'oylik',
                'amount'  => (float) $p->amount,
                'status'  => 'yechib olingan',
            ];
        }
        foreach ($advances as $a) {
            $withdrawn += (float) $a->amount;
            $txns[] = [
                'type'    => 'avans',
                'dir'     => 'out',
                'date'    => $a->given_at,
                'owner'   => 'Avans',
                'number'  => $a->month ?? '',
                'service' => 'avans',
                'amount'  => (float) $a->amount,
                'status'  => 'yechib olingan',
            ];
        }

        // Sana bo'yicha kamayish tartibida
        usort($txns, fn ($x, $y) => ($y['date'] <=> $x['date']));

        $balance = $earned - $withdrawn;

        return [
            'user_id'   => $userId,
            'user_name' => $user->name,
            'rate'      => $rate,
            'earned'    => $earned,       // tasdiqlangan kirim
            'pending'   => $pending,      // jarayonda
            'withdrawn' => $withdrawn,    // to'langan (chiqim)
            'balance'   => $balance,      // firma qarzi (balans)
            'txns'      => $txns,
            'txn_count' => count($txns),
        ];
    }

    /**
     * Loyiha to'lovlaridan shu xizmatga to'g'ri keladigan summani hisoblaymiz.
     * To'lov 'services' bo'yicha taqsimlanadi (ProjectEditModal'dagi mantiq bilan bir xil).
     * Agar to'lov xizmatlarga biriktirilmagan bo'lsa — narx ulushiga qarab taqsimlanadi.
     */
    private static function paidForService(ProjectService $s): float
    {
        $project = $s->project;
        if (!$project) return 0.0;

        $priceMap = [];
        foreach ($project->services as $svc) {
            $priceMap[$svc->service_name] = (float) $svc->final_price;
        }
        $totalPrice = array_sum($priceMap);
        $myPrice    = $priceMap[$s->service_name] ?? 0;

        $paid = 0.0;
        foreach ($project->payments as $pay) {
            $svcs = $pay->services ?? [];

            if (empty($svcs)) {
                // Biriktirilmagan to'lov — barcha xizmatlar narx ulushiga qarab
                if ($totalPrice > 0) {
                    $paid += (float) $pay->amount * ($myPrice / $totalPrice);
                }
                continue;
            }

            if (!in_array($s->service_name, $svcs)) continue;

            $sumSel = 0.0;
            foreach ($svcs as $sn) $sumSel += ($priceMap[$sn] ?? 0);
            $paid += $sumSel > 0
                ? (float) $pay->amount * ($myPrice / $sumSel)
                : (float) $pay->amount / max(1, count($svcs));
        }

        return $paid;
    }

    private static function empty(): array
    {
        return [
            'user_id' => 0, 'user_name' => '', 'rate' => 0,
            'earned' => 0, 'pending' => 0, 'withdrawn' => 0, 'balance' => 0,
            'txns' => [], 'txn_count' => 0,
        ];
    }
}
