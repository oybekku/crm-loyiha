<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountTransfer extends Model
{
    protected $fillable = [
        'from_account_id', 'to_account_id', 'amount', 'transfer_date', 'comment', 'created_by', 'month', 'to_month',
    ];

    /** Pul QAYSI OYDAN chiqadi (Y-m): `month`, bo'lmasa o'tkazma sanasi oyi */
    public function scopeOutOfMonth($q, string $ym)
    {
        [$y, $m] = explode('-', $ym);
        return $q->where(fn ($w) => $w->where('month', $ym)
            ->orWhere(fn ($w2) => $w2->whereNull('month')->whereYear('transfer_date', $y)->whereMonth('transfer_date', $m)));
    }

    /** Pul QAYSI OYGA kiradi (Y-m): `to_month`, bo'lmasa chiqqan oyning o'zi */
    public function scopeIntoMonth($q, string $ym)
    {
        [$y, $m] = explode('-', $ym);
        return $q->where(fn ($w) => $w->where('to_month', $ym)
            ->orWhere(fn ($w2) => $w2->whereNull('to_month')->where(fn ($w3) => $w3->where('month', $ym)
                ->orWhere(fn ($w4) => $w4->whereNull('month')->whereYear('transfer_date', $y)->whereMonth('transfer_date', $m)))));
    }

    /** Oydan oyga o'tkazmami (chiqqan va kirgan oy har xil) */
    public function getIsCrossMonthAttribute(): bool
    {
        $from = $this->month ?: $this->transfer_date?->format('Y-m');
        return $this->to_month !== null && $this->to_month !== $from;
    }

    protected $casts = [
        'amount'        => 'decimal:2',
        'transfer_date' => 'date',
    ];

    public function fromAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'to_account_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
