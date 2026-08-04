<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCommissionRate extends Model
{
    protected $fillable = [
        'user_id', 'rate', 'effective_month', 'created_by',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
