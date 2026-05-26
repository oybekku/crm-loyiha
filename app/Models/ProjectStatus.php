<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectStatus extends Model
{
    protected $fillable = ['key', 'label', 'color', 'sort_order', 'is_archive'];

    protected $casts = ['is_archive' => 'boolean'];

    public static function allOrdered(): \Illuminate\Database\Eloquent\Collection
    {
        return static::orderBy('sort_order')->orderBy('id')->get();
    }

    public static function asOptions(): array
    {
        return static::allOrdered()->pluck('label', 'key')->toArray();
    }
}
