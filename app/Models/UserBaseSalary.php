<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Oklad tarixi: "effective_month oydan boshlab amount" (EmployeePayableService::okladFor) */
class UserBaseSalary extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'effective_month', 'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    protected static function booted(): void
    {
        // EmployeePayableService::okladFor() xotirasi eskirmasin
        $flush = fn () => \App\Services\EmployeePayableService::flushOkladCache();
        static::saved($flush);
        static::deleted($flush);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
