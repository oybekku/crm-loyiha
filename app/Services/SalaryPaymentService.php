<?php

namespace App\Services;

use App\Models\EmployeeSalaryPayment;
use App\Models\Expense;
use App\Models\FinancialAccount;
use Carbon\Carbon;

/**
 * Xodimga ish haqi berish (EmployeeSalaryPayment) — Oylik hisobot va
 * "Yangi bux" → Oylik maosh sahifalari uchun YAGONA manba, shu bilan ikkala
 * joydan berilgan oylik bir xil yoziladi va Buxgalteriyaga bir xil tushadi.
 */
class SalaryPaymentService
{
    /**
     * $accountId — oylik qaysi hisobdan berilgani ("Yangi bux"da admin tanlaydi).
     * Berilmasa (Oylik hisobot) — avvalgidek "Xarajatlar hisobi" belgisi.
     */
    public static function save(array $data, ?int $editId = null, bool $isPartial = false, ?int $accountId = null): ?EmployeeSalaryPayment
    {
        if ($editId) {
            $payment = EmployeeSalaryPayment::find($editId);
            $payment?->update($data);
        } else {
            $payment = EmployeeSalaryPayment::create($data);
        }

        if ($payment) self::syncExpense($payment, $isPartial, $accountId);

        return $payment;
    }

    /**
     * Mavjud oddiy chiqimni xodimning oylik/avans to'loviga aylantiradi:
     * yangi EmployeeSalaryPayment yaratiladi va o'sha chiqim qatori unga
     * bog'lanadi (yangi xarajat YARATILMAYDI — pul ikki marta hisoblanmaydi).
     * Summa, sana va hisob chiqimdagidek qoladi.
     */
    public static function attachExpense(Expense $expense, int $userId, string $month): EmployeeSalaryPayment
    {
        $payment = EmployeeSalaryPayment::create([
            'user_id'  => $userId,
            'month'    => $month,
            'amount'   => $expense->amount,
            'paid_at'  => $expense->expense_date,
            'note'     => $expense->comment,
            'given_by' => auth()->id(),
        ]);

        $expense->update([
            'salary_payment_id' => $payment->id,
            'user_id'           => $userId,
            'responsible_id'    => $userId,
            'category'          => Expense::KIND_OYLIK,
            'month'             => $month,
        ]);

        return $payment;
    }

    public static function delete(int $paymentId): void
    {
        Expense::where('salary_payment_id', $paymentId)->delete();
        EmployeeSalaryPayment::find($paymentId)?->delete();
    }

    // Ish haqi to'lovini (EmployeeSalaryPayment) Buxgalteriyaning "Xarajatlar
    // hisobi" (is_expense_account) belgilangan hisobiga bog'liq xarajat
    // qatori sifatida yozadi/yangilaydi — to'lov bilan 1:1 bog'langan
    // (salary_payment_id orqali), shu sabab to'lov tahrirlansa/o'chirilsa
    // shu qator ham sinxron o'zgaradi. "Xarajatlar hisobi" belgilanmagan
    // bo'lsa — hech narsa yozilmaydi (jim o'tkazib yuboriladi).
    public static function syncExpense(EmployeeSalaryPayment $payment, bool $isPartial, ?int $accountId = null): void
    {
        // Ustuvorlik: aniq tanlangan hisob → shu to'lovning mavjud xarajati
        // hisobi (tahrirlashda o'zgarmasin) → "Xarajatlar hisobi" belgisi.
        $expenseAccountId = $accountId
            ?: Expense::where('salary_payment_id', $payment->id)->value('account_id')
            ?: FinancialAccount::where('is_expense_account', true)->value('id');
        if (!$expenseAccountId) return;

        $monthLabel = Carbon::createFromFormat('Y-m', $payment->month)->translatedFormat('F Y');
        $kind       = $isPartial ? "qisman avans" : 'oylik';
        $userName   = $payment->user?->name ?? $payment->user_id;
        $comment    = "{$userName} — {$monthLabel} oyi uchun {$kind} berildi";

        Expense::updateOrCreate(
            ['salary_payment_id' => $payment->id],
            [
                'account_id'   => $expenseAccountId,
                'user_id'      => $payment->user_id,
                'category'     => Expense::KIND_OYLIK,
                'month'        => $payment->month,
                'amount'       => $payment->amount,
                'comment'      => $comment,
                'expense_date' => $payment->paid_at,
                'created_by'   => auth()->id(),
            ]
        );
    }
}
