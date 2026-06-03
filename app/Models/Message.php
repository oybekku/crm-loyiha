<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['from_user_id', 'to_user_id', 'body', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function sender()   { return $this->belongsTo(User::class, 'from_user_id'); }
    public function receiver() { return $this->belongsTo(User::class, 'to_user_id'); }
}
