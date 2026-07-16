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

    /** Balans — shu hisobga biriktirilgan barcha to'lovlar yig'indisi.
     *  withSum('payments','amount') orqali oldindan yuklangan bo'lsa o'shani
     *  ishlatadi (tez), aks holda to'g'ridan-to'g'ri so'rov yuboradi. */
    public function getBalanceAttribute(): float
    {
        if (array_key_exists('payments_sum_amount', $this->attributes)) {
            return (float) $this->attributes['payments_sum_amount'];
        }
        return (float) $this->payments()->sum('amount');
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
