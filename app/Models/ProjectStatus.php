<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ProjectStatus extends Model
{
    protected $fillable = ['key', 'label', 'color', 'sort_order', 'is_archive'];

    protected $casts = ['is_archive' => 'boolean'];

    protected static function booted(): void
    {
        // Statuslar o'zgarganda cache tozalanadi
        static::saved(fn()   => Cache::forget('project_statuses_ordered'));
        static::deleted(fn() => Cache::forget('project_statuses_ordered'));
    }

    public static function allOrdered(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('project_statuses_ordered', 600, function () {
            return static::orderBy('sort_order')->orderBy('id')->get();
        });
    }

    public static function asOptions(): array
    {
        return static::allOrdered()->pluck('label', 'key')->toArray();
    }
}
