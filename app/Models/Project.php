<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'number', 'owner_name', 'title', 'address', 'phones',
        'description', 'category', 'status', 'assigned_user_id',
        'total_price', 'paid_amount', 'deadline_date',
    ];

    protected $casts = [
        'phones'        => 'array',
        'total_price'   => 'float',
        'paid_amount'   => 'float',
        'deadline_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function ($project) {
            if (empty($project->number)) {
                $project->number = '#' . str_pad(random_int(1, 999999999), 9, '0', STR_PAD_LEFT);
            }
        });
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'project_user')->withTimestamps();
    }

    public function services()
    {
        return $this->hasMany(ProjectService::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function files()
    {
        return $this->hasMany(ProjectFile::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(ProjectStatusLog::class)->orderBy('entered_at');
    }

    public function currentStatusLog()
    {
        return $this->hasOne(ProjectStatusLog::class)->whereNull('left_at')->latest('entered_at');
    }

    public function getDeadlineDaysLeftAttribute(): ?int
    {
        if (!$this->deadline_date) return null;
        return (int) now()->startOfDay()->diffInDays($this->deadline_date, false);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float)$this->total_price - (float)$this->paid_amount);
    }

    public function getPaymentPercentAttribute(): int
    {
        if ($this->total_price <= 0) return 0;
        return min(100, (int) round(($this->paid_amount / $this->total_price) * 100));
    }

    public function updateTotals(): void
    {
        $this->total_price = $this->services()->sum('final_price');
        $this->paid_amount = $this->payments()->sum('amount');
        $this->saveQuietly();
    }

    public static function statusOptions(): array
    {
        return [
            'yangi'            => 'Yangi',
            'tolov_jarayonida' => "To'lov jarayonida",
            'eskiz_loyiha'     => 'Eskiz loyiha',
            'tekshirish'       => 'Tekshirish',
            'tolangan'         => "To'langan",
            'tugallangan'      => 'Tugallangan',
            'taqdim_etilgan'   => 'Taqdim etilgan',
            'bekor_qilingan'   => 'Bekor qilingan',
        ];
    }

    public static function categoryOptions(): array
    {
        return [
            'turar'   => 'Turar joy',
            'noturar' => 'Noturar joy',
        ];
    }

    public static function serviceOptions(): array
    {
        return [
            'toposyomka'  => 'Toposyomka',
            'eskiz_loyiha'=> 'Eskiz loyiha',
            'ariza'       => 'Ariza',
        ];
    }
}
