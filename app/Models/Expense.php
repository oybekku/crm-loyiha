<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'account_id', 'user_id', 'salary_payment_id', 'month', 'amount', 'comment', 'expense_date', 'created_by',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(FinancialAccount::class, 'account_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function salaryPayment()
    {
        return $this->belongsTo(EmployeeSalaryPayment::class, 'salary_payment_id');
    }

    /**
     * Xodimga berilgan REAL ish haqi to'lovidan (EmployeeSalaryPayment)
     * avtomatik yozilgan xarajat qatormi — bunday qatorlar qo'lda
     * tahrirlanmaydi/o'chirilmaydi, chunki bog'liq to'lov o'zgartirilsa/
     * o'chirilsa shu qator ham avtomatik sinxron yangilanadi/o'chiriladi
     * (Oylik hisobot -> "To'lanishi kerak" jadvali orqali).
     */
    public function getIsAutoAttribute(): bool
    {
        return !is_null($this->user_id);
    }
}
