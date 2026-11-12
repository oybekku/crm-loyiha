<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'project_id', 'amount', 'payment_date', 'method', 'account_id', 'note', 'created_by', 'services', 'service_split',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'payment_date'  => 'date',
        'services'      => 'array',
        'service_split' => 'array',
    ];

    protected static function booted(): void
    {
        // Bazadagi loyiha to'lovlari faqat o'qish uchun (kassa qoldig'i o'zgarmasin)
        $guard = function ($payment) {
            if (\App\Services\BazaService::$bypass) return;
            $pid = $payment->project_id ?? $payment->getOriginal('project_id');
            if ($pid && \App\Services\BazaService::isBazaProjectId((int) $pid)) {
                throw new \RuntimeException("Bu to'lov Bazadagi loyihaga tegishli — o'zgartirib bo'lmaydi.");
            }
        };
        static::creating($guard);
        static::updating($guard);
        static::deleting($guard);

        static::saved(function ($payment) {
            $payment->project?->updateTotals();
        });

        static::deleted(function ($payment) {
            $payment->project?->updateTotals();
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class)->withoutGlobalScope(\App\Models\Scopes\HideBazaScope::class); // Baza: pul/ish izi saqlanadi
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function account()
    {
        return $this->belongsTo(FinancialAccount::class, 'account_id');
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
