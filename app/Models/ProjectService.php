<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectService extends Model
{
    protected $fillable = [
        'project_id', 'assigned_user_id', 'service_name', 'price',
        'discount_type', 'discount_value', 'final_price', 'note',
        'deadline_days', 'work_started_at', 'completed_at',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'discount_value' => 'decimal:2',
        'final_price'    => 'decimal:2',
        'work_started_at' => 'datetime',
        'completed_at'    => 'datetime',
        'deadline_days'  => 'integer',
    ];

    public function getDeadlineDateAttribute(): ?\Carbon\Carbon
    {
        if (!$this->work_started_at || !$this->deadline_days) return null;
        return $this->work_started_at->copy()->addDays($this->deadline_days);
    }

    public function getIsLateAttribute(): bool
    {
        $deadline = $this->deadline_date;
        if (!$deadline) return false;
        return now()->gt($deadline);
    }

    public function getLateDaysAttribute(): int
    {
        if (!$this->is_late) return 0;
        return (int) $this->deadline_date->diffInDays(now());
    }


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
