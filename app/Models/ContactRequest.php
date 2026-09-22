<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactRequest extends Model
{
    public const TYPE_ZAYAVKA  = 'zayavka';
    public const TYPE_QONGIROQ = 'qongiroq';

    protected $fillable = ['full_name', 'phone', 'message', 'is_handled', 'type'];

    protected $casts = [
        'is_handled' => 'boolean',
    ];
}
