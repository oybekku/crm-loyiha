<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'project_id', 'amount', 'payment_date', 'method', 'note', 'created_by', 'services',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
        'services'     => 'array',
    ];

    protected static function booted(): void
    {
        static::saved(function ($payment) {
            $payment->project?->updateTotals();
        });

        static::deleted(function ($payment) {
            $payment->project?->updateTotals();
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function methodOptions(): array
    {
        return [
            'naqd'  => 'Naqd pul',
            'bank'  => "Bank o'tkazma",
            'karta' => 'Karta',
        ];
    }
}
