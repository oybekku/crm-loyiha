<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Doimiy (har oylik) to'lov shabloni — arenda, svet, wi-fi... */
class RecurringExpense extends Model
{
    protected $fillable = ['name', 'amount', 'is_fixed', 'account_id', 'due_day', 'is_active', 'sort_order'];

    protected $casts = [
        'amount'    => 'decimal:2',
        'is_fixed'  => 'boolean',
        'is_active' => 'boolean',
    ];

    public function account()
    {
        return $this->belongsTo(FinancialAccount::class, 'account_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
