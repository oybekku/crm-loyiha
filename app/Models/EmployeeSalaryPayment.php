<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryPayment extends Model
{
    protected $fillable = ['user_id', 'month', 'amount', 'paid_at', 'note', 'given_by'];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'date',
    ];

    public function user()   { return $this->belongsTo(User::class); }
    public function giver()  { return $this->belongsTo(User::class, 'given_by'); }
}
