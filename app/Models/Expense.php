<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'account_id', 'user_id', 'month', 'amount', 'comment', 'expense_date', 'created_by',
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

    /**
     * Xodim komissiyasidan EmployeePayableService orqali avtomatik yozilgan
     * xarajat qatormi — bunday qatorlar qo'lda tahrirlanmaydi/o'chirilmaydi,
     * chunki har sahifa yuklanganda qayta hisoblab yoziladi.
     */
    public function getIsAutoAttribute(): bool
    {
        return !is_null($this->user_id);
    }
}
