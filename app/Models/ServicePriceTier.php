<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ServicePriceTier extends Model
{
    protected $fillable = [
        'service_key',
        'sub_service',
        'sub_service_label',
        'label',
        'price',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saved(fn()   => Cache::forget('price_tiers_grouped'));
        static::deleted(fn() => Cache::forget('price_tiers_grouped'));
    }

    public static function forService(string $serviceKey): array
    {
        return static::where('service_key', $serviceKey)
            ->orderBy('sub_service')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('sub_service')
            ->toArray();
    }
}
