<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectService extends Model
{
    protected $fillable = [
        'project_id', 'assigned_user_id', 'service_name', 'price',
        'discount_type', 'discount_value', 'final_price', 'note',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'discount_value' => 'decimal:2',
        'final_price'    => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function ($service) {
            $price = (float) $service->price;
            if ($service->discount_type === 'percent') {
                $service->final_price = $price - ($price * $service->discount_value / 100);
            } elseif ($service->discount_type === 'fixed') {
                $service->final_price = max(0, $price - $service->discount_value);
            } else {
                $service->final_price = $price;
            }
        });

        static::saved(function ($service) {
            $service->project?->updateTotals();
            // Xizmatga biriktirilgan hodimni loyihaning assignedUsers ga ham qo'shish
            if ($service->assigned_user_id && $service->project) {
                $service->project->assignedUsers()->syncWithoutDetaching([$service->assigned_user_id]);
            }
        });

        static::deleted(function ($service) {
            $service->project?->updateTotals();
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function getServiceLabelAttribute(): string
    {
        return Project::serviceOptions()[$this->service_name] ?? $this->service_name;
    }
}
