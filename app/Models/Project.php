<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'number', 'owner_name', 'title', 'address', 'latitude', 'longitude', 'phones',
        'description', 'category', 'status', 'assigned_user_id',
        'total_price', 'paid_amount', 'deadline_date',
        'payment_requested_at', 'payment_requested_by',
    ];

    protected $casts = [
        'phones'                => 'array',
        'total_price'           => 'float',
        'paid_amount'           => 'float',
        'latitude'              => 'float',
        'longitude'             => 'float',
        'deadline_date'         => 'date',
        'payment_requested_at'  => 'datetime',
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

    public function paymentRequester()
    {
        return $this->belongsTo(User::class, 'payment_requested_by');
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

    public function paymentLogs()
    {
        return $this->hasMany(\App\Models\PaymentLog::class)->with('user')->orderByDesc('created_at');
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

        if ($this->total_price <= 0) return;

        $archiveStatuses = ['tugallangan', 'taqdim_etilgan', 'bekor_qilingan'];
        if (in_array($this->status, $archiveStatuses)) return;

        $newStatus = null;

        if ($this->paid_amount >= $this->total_price) {
            // To'liq to'langan
            if ($this->status === 'yangi') {
                // Yangi loyihada xizmatlar hali boshlanmagan → toposyomka (ish boshlash kerak)
                $newStatus = 'toposyomka';
            } elseif (in_array($this->status, ['tolov_jarayonida'])) {
                // To'lov jarayonida edi, to'liq to'landi → toposyomka (ish boshlash kerak)
                $newStatus = 'toposyomka';
            } elseif (!in_array($this->status, ['tolangan', 'toposyomka'])) {
                // Ish allaqachon boshlangan (eskiz, tekshirish...) + to'liq to'lov → tolangan
                $newStatus = 'tolangan';
            }
        } elseif ($this->paid_amount > 0) {
            // Qisman to'langan → toposyomka (agar yangi yoki tolov_jarayonida bo'lsa)
            if (in_array($this->status, ['yangi', 'tolov_jarayonida'])) {
                $newStatus = 'toposyomka';
            }
        }

        if ($newStatus) {
            $this->status = $newStatus;
            $this->saveQuietly();

            ProjectStatusLog::where('project_id', $this->id)
                ->whereNull('left_at')
                ->update(['left_at' => now()]);

            ProjectStatusLog::create([
                'project_id' => $this->id,
                'status'     => $newStatus,
                'entered_at' => now(),
                'changed_by' => auth()->id(),
            ]);
        }
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
