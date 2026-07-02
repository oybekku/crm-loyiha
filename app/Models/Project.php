<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'number', 'owner_name', 'title', 'address', 'oblozhka_address', 'signature_path', 'latitude', 'longitude', 'phones',
        'description', 'category', 'status', 'assigned_user_id',
        'total_price', 'paid_amount', 'deadline_date', 'timer_paused_at',
        'payment_requested_at', 'payment_requested_by',
    ];

    protected $casts = [
        'phones'                => 'array',
        'total_price'           => 'float',
        'paid_amount'           => 'float',
        'latitude'              => 'float',
        'longitude'             => 'float',
        'deadline_date'         => 'date',
        'timer_paused_at'       => 'datetime',
        'payment_requested_at'  => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($project) {
            if (empty($project->number)) {
                $project->number = '#' . str_pad(random_int(1, 999999999), 9, '0', STR_PAD_LEFT);
            }
        });

        static::updated(function ($project) {
            // Status o'zgarganda — mos xizmat timerini ishga tushirish
            if (!$project->wasChanged('status')) return;

            $newStatus = $project->status;
            $prevStatus = $project->getOriginal('status');
            $now       = now();

            // Joriy statusga mos xizmatni topamiz va work_started_at ni belgilaymiz
            ProjectService::where('project_id', $project->id)
                ->where('service_name', $newStatus)
                ->whereNull('work_started_at')  // faqat boshlanmagan bo'lsa
                ->update(['work_started_at' => $now]);

            // Ish bosqichlari (hodim ishlaydigan)
            $workStages = ['toposyomka', 'eskiz_loyiha'];

            // Ish bosqichidan CHIQILDI (tekshirishga/keyingiga yuborildi) → submitted_at muzlatish
            if (in_array($prevStatus, $workStages) && $prevStatus !== $newStatus) {
                ProjectService::where('project_id', $project->id)
                    ->where('service_name', $prevStatus)
                    ->whereNotNull('work_started_at')
                    ->whereNull('submitted_at')
                    ->whereNull('completed_at')
                    ->update(['submitted_at' => $now]);
            }

            // Ish bosqichiga QAYTDI (qayta ishlash) → submitted_at tozalanadi, timer davom etadi
            if (in_array($newStatus, $workStages)) {
                ProjectService::where('project_id', $project->id)
                    ->where('service_name', $newStatus)
                    ->whereNull('completed_at')
                    ->update(['submitted_at' => null]);
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
        // Faqat pul summalarini yangilaymiz. Status'ga TEGMAYMIZ —
        // to'lov qilinishi loyihani avtomatik boshqa bo'limga ko'chirmaydi.
        // Status'ni faqat foydalanuvchi qo'lda o'zgartiradi (Kanban / tahrirlash).
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
