<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeInventory extends Model
{
    protected $fillable = [
        'user_id', 'given_by', 'name', 'image', 'quantity', 'price',
        'status', 'given_at', 'returned_at', 'note',
    ];

    protected $casts = [
        'image'       => 'array',
        'price'       => 'decimal:2',
        'quantity'    => 'integer',
        'given_at'    => 'date',
        'returned_at' => 'date',
    ];

    public const STATUSES = [
        'berilgan'    => 'Berilgan',
        'qaytarilgan' => 'Qaytarilgan',
        'yaroqsiz'    => 'Yaroqsiz',
        'yoqolgan'    => "Yo'qolgan",
    ];

    // Jami qiymat = miqdor * narx
    public function getTotalAttribute(): float
    {
        return (float) $this->quantity * (float) $this->price;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function giver()
    {
        return $this->belongsTo(User::class, 'given_by');
    }
}
