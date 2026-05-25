<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectStatusLog extends Model
{
    protected $fillable = [
        'project_id', 'status', 'entered_at', 'left_at',
        'allocated_days', 'assigned_user_id',
    ];

    protected $casts = [
        'entered_at' => 'datetime',
        'left_at'    => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function getDaysInStatusAttribute(): int
    {
        $end = $this->left_at ?? now();
        return (int) $this->entered_at->diffInDays($end);
    }

    public function getDelayDaysAttribute(): int
    {
        if ($this->allocated_days === 0) return 0;
        return max(0, $this->days_in_status - $this->allocated_days);
    }

    public function isDelayed(): bool
    {
        return $this->delay_days > 0;
    }
}
