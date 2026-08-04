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
 *  - Bir ish komissiyasi = ish narxi (final_price, chegirmadan keyingi) × xodim foizi,
 *    EmployeePayableService::commissionForService orqali — bu Oylik hisobot va
 *    Buxgalteriya bilan AYNAN BIR XIL formula (loyihaning umumiy to'lov nisbatiga
 *    mutanosib "ochilgan" ulush). Avval bu yerda alohida, qattiqroq qoida bor edi
 *    ("faqat 100% to'langan ish hisoblanadi") — natijada shu oyna boshqa sahifalar
 *    bilan mos kelmay, hodim "ortiqcha to'langan" bo'lsa ham "firma qarzdor" deb
 *    ko'rsatib yuborardi. Endi hammasi bitta manbadan.
 *  - "Tasdiqlangan kirim" = mijoz to'lagan ulushga mutanosib ochilgan komissiya
 *    (comm_paid) — tugallangan ishlar bo'yicha.
 *  - "Jarayonda" = hali "ochilmagan" qism (comm_remaining) — tugallangan ishning
 *    mijoz hali to'lamagan qismi HAM, hali tugallanmagan ishlar HAM shu yerga kiradi.
 *  - "Chiqim" = xodim olgan oylik (EmployeeSalaryPayment) + avanslar (EmployeeAdvance).
 *  - "Balans" = tasdiqlangan kirim − chiqim. Manfiy bo'lsa — bu "firma qarzi" EMAS,
 *    aksincha hodimga ortiqcha to'langan degani (Oylik hisobotdagi "Ortiqcha
 *    to'langan" bilan bir xil son, faqat belgisi teskari).
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

        // Admin/menejer har doim 0% — butun blokni tashlab yuboramiz. Boshqa
        // hodimlar uchun foiz endi HAR BIR xizmat o'ziga tegishli loyiha
        // ochilgan oyiga qarab alohida aniqlanadi (EmployeePayableService::
        // rateFor) — chunki bitta hodimning turli oylardagi ishlari turli
        // foizga tegishli bo'lishi mumkin (foiz o'zgargan bo'lsa).
        $isRated = !in_array($user->role, ['admin', 'menejer']);

        $monthStr = ($year && $month) ? sprintf('%04d-%02d', $year, $month) : null;

        $earned  = 0.0;   // tasdiqlangan kirim (tugatilgan + to'langan)
        $pending = 0.0;   // jarayonda
        $txns    = [];
        $rate    = 0.0;   // qaytariladigan "umumiy" foiz — ko'rsatish uchun (pastda)

        if ($isRated) {
            $services = ProjectService::with('project')
                ->where('assigned_user_id', $userId)
                ->whereHas('project', function ($q) use ($year, $month) {
                    $q->where('status', '!=', 'bekor_qilingan');
                    if ($year && $month) {
                        $q->whereYear('created_at', $year)->whereMonth('created_at', $month);
                    }
                })
                ->get();

            foreach ($services as $s) {
                $price = (float) $s->final_price;
                if ($price <= 0) continue;

                $calc = EmployeePayableService::commissionForService($s, $s->project);
                $rate = $calc['rate']; // ko'rsatish uchun — oxirgi ko'rilgan xizmat foizi

                $commission = $calc['commission'];
                if ($commission <= 0) continue;

                // Faqat tugallangan ish uchun "ochilgan" (comm_paid) qism hisoblanadi —
                // hali tugallanmagan ish butunlay "jarayonda" hisoblanadi (Oylik
                // hisobotdagi "kutayotgan" bilan bir xil mantiq).
                $isCompleted = (bool) $s->completed_at;
                $commPaid    = $isCompleted ? $calc['comm_paid'] : 0.0;
                $commLeft    = $commission - $commPaid;

                $earned  += $commPaid;
                $pending += $commLeft;

                $status = !$isCompleted
                    ? 'jarayonda'
                    : ($commPaid >= $commission - 0.01 ? 'tasdiqlangan' : "qisman to'langan");

                $txns[] = [
                    'type'    => 'ish',
                    'dir'     => 'in',
                    'date'    => ($s->completed_at ?? $s->created_at),
                    'owner'   => $s->project?->owner_name ?? '—',
                    'number'  => $s->project?->number ?? '',
                    'service' => Project::serviceOptions()[$s->service_name] ?? $s->service_name,
                    'amount'  => $commission,
                    'status'  => $status,
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
            'earned'    => $earned,       // tasdiqlangan kirim (mijoz to'lagan ulushga mutanosib)
            'pending'   => $pending,      // jarayonda (hali "ochilmagan" qism)
            'withdrawn' => $withdrawn,    // to'langan (chiqim)
            'balance'   => $balance,      // manfiy = ortiqcha to'langan (firma qarzi emas)
            'txns'      => $txns,
            'txn_count' => count($txns),
        ];
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
