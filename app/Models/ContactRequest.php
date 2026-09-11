<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactRequest extends Model
{
    protected $fillable = ['full_name', 'phone', 'message', 'is_handled'];

    protected $casts = [
        'is_handled' => 'boolean',
    ];
}
