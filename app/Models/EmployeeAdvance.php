<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAdvance extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'given_by', 'amount', 'month', 'note', 'given_at'];

    protected $casts = [
        'amount'   => 'decimal:2',
        'given_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function giver()
    {
        return $this->belongsTo(User::class, 'given_by');
    }
}
