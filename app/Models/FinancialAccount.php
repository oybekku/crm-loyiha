<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialAccount extends Model
{
    protected $fillable = [
        'type', 'name', 'card_number', 'bank_name', 'expiry_date',
        'account_number', 'is_favorite', 'sort_order',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'account_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'account_id');
    }

    /** Balans — kirim (to'lovlar) minus chiqim (xarajatlar).
     *  withSum('payments','amount') / withSum('expenses','amount') orqali
     *  oldindan yuklangan bo'lsa o'shani ishlatadi (tez), aks holda
     *  to'g'ridan-to'g'ri so'rov yuboradi. */
    public function getBalanceAttribute(): float
    {
        $income = array_key_exists('payments_sum_amount', $this->attributes)
            ? (float) $this->attributes['payments_sum_amount']
            : (float) $this->payments()->sum('amount');

        $spent = array_key_exists('expenses_sum_amount', $this->attributes)
            ? (float) $this->attributes['expenses_sum_amount']
            : (float) $this->expenses()->sum('amount');

        return $income - $spent;
    }

    public static function typeOptions(): array
    {
        return [
            'karta' => 'Karta',
            'naqd'  => "Naqd pul",
            'bank'  => 'Bank hisob raqami',
        ];
    }
}
