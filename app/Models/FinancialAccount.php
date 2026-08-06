<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialAccount extends Model
{
    protected $fillable = [
        'type', 'name', 'card_number', 'bank_name', 'expiry_date',
        'account_number', 'is_favorite', 'sort_order', 'is_personal',
        'background_image', 'is_secondary', 'is_expense_account', 'is_commission_source',
    ];

    protected $casts = [
        'is_favorite'           => 'boolean',
        'is_personal'           => 'boolean',
        'is_secondary'          => 'boolean',
        'is_expense_account'    => 'boolean',
        'is_commission_source'  => 'boolean',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'account_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'account_id');
    }

    public function transfersIn()
    {
        return $this->hasMany(AccountTransfer::class, 'to_account_id');
    }

    public function transfersOut()
    {
        return $this->hasMany(AccountTransfer::class, 'from_account_id');
    }

    /** Balans — kirim (to'lovlar + kelgan o'tkazmalar) minus chiqim (xarajatlar +
     *  ketgan o'tkazmalar). withSum(...) orqali oldindan yuklangan bo'lsa o'shani
     *  ishlatadi (tez), aks holda to'g'ridan-to'g'ri so'rov yuboradi. */
    public function getBalanceAttribute(): float
    {
        $income = array_key_exists('payments_sum_amount', $this->attributes)
            ? (float) $this->attributes['payments_sum_amount']
            : (float) $this->payments()->sum('amount');

        $spent = array_key_exists('expenses_sum_amount', $this->attributes)
            ? (float) $this->attributes['expenses_sum_amount']
            : (float) $this->expenses()->sum('amount');

        $transfersIn = array_key_exists('transfers_in_sum_amount', $this->attributes)
            ? (float) $this->attributes['transfers_in_sum_amount']
            : (float) $this->transfersIn()->sum('amount');

        $transfersOut = array_key_exists('transfers_out_sum_amount', $this->attributes)
            ? (float) $this->attributes['transfers_out_sum_amount']
            : (float) $this->transfersOut()->sum('amount');

        return $income - $spent + $transfersIn - $transfersOut;
    }

    /** "Xarajatlar hisobi" kartasida ko'rsatish uchun — shu hisobga yozilgan
     *  BARCHA xarajatlar (komissiya, oylik, boshqa) yig'indisi, manfiy son
     *  sifatida. getBalanceAttribute()dan farqli o'laroq, kirim (o'tkazma)
     *  bilan tenglashtirilmaydi — shu bilan "qancha xarajat qilindi" doim
     *  ko'rinib turadi, hatto manba hisobdan pul o'tkazilgan bo'lsa ham.
     *  "Jami balans" hisob-kitobiga bu emas, getBalanceAttribute() ishlatiladi
     *  — aks holda summa ikki marta hisoblanib qolardi. */
    public function getExpenseTotalAttribute(): float
    {
        $spent = array_key_exists('expenses_sum_amount', $this->attributes)
            ? (float) $this->attributes['expenses_sum_amount']
            : (float) $this->expenses()->sum('amount');

        return -$spent;
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
