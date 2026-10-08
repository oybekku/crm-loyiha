<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'number', 'seq_no', 'owner_name', 'title', 'address', 'oblozhka_address', 'signature_path', 'latitude', 'longitude', 'phones',
        'passport_series', 'passport_issued_by', 'pinfl',
        'applicant_type', 'cadastre_number', 'region', 'district', 'registration_basis', 'ownership_document_path',
        'description', 'category', 'status', 'is_didox', 'work_status', 'assigned_user_id',
        'total_price', 'paid_amount', 'deadline_date', 'timer_paused_at', 'invoice_sent_at', 'invoice_sent_by',
        'didox_added_at', 'didox_added_by', 'didox_contract_done_at', 'didox_contract_done_by',
        'payment_requested_at', 'payment_requested_by',
        'mygov_login', 'mygov_password', 'mygov_fish',
        'is_urgent', 'urgent_accepted_at', 'urgent_accepted_by',
        'ready_sms_status', 'ready_sms_sent_at', 'ready_sms_error',
        'debt_comment', 'debt_called', 'debt_called_at', 'debt_called_by',
        'work_checklist',
    ];

    /**
     * "Qilinadigan ishlar ro'yxati" — har loyihada doim bor standart ishlar.
     * Kalitni o'zgartirmang (bazada saqlangan galochkalar shu kalitlarga bog'langan).
     */
    public const WORK_CHECKLIST_ITEMS = [
        'kadastr_yangilash' => 'Kadastr yangilash',
        'topo_semka'        => 'Topo s\'emka',
        'eskiz_loyiha'      => 'Eskiz loyiha',
        'loyiha_smeta'      => 'Loyiha-smeta xujjatlari',
        'texnik_pasport'    => 'Texnik pasport',
        'foydalanishga_rk'  => 'Foydalanishga r.k.',
        'akt_loyiha_tash'   => 'AKT loyiha tashkilot ma\'lumotnomasi',
        'qurilish_tugallanmagan' => 'Qurilishi tugallanmagan ob\'ekt kad. pasporti',
        'akt_63'            => '63% akt ma\'lumotnomasi',
        'royxat_ariza'      => 'Ro\'yxatdan o\'tkazish uchun ariza',
    ];

    /** Ro'yxat (standart + qo'shimcha) — [['key','label','done','extra'], ...] */
    public function workChecklist(): array
    {
        $data  = $this->work_checklist ?: [];
        $done  = $data['done'] ?? [];
        $items = [];
        foreach (self::WORK_CHECKLIST_ITEMS as $key => $label) {
            $items[] = ['key' => $key, 'label' => $label, 'done' => in_array($key, $done, true), 'extra' => false];
        }
        foreach ($data['extra'] ?? [] as $e) {
            $items[] = ['key' => $e['id'], 'label' => $e['label'], 'done' => (bool) ($e['done'] ?? false), 'extra' => true];
        }
        return $items;
    }

    /**
     * Shu so'rov davomida yuborilgan "tayyor" SMS natijalari.
     * KanbanBoard::dehydrate() buni o'qib, ekranda toast (xabar oynasi) chiqaradi.
     * @var array<int, array{ok:bool, message:string}>
     */
    public static array $pendingSmsNotifications = [];

    protected $casts = [
        'phones'                => 'array',
        'total_price'           => 'float',
        'paid_amount'           => 'float',
        'latitude'              => 'float',
        'longitude'             => 'float',
        'deadline_date'         => 'date',
        'timer_paused_at'       => 'datetime',
        'invoice_sent_at'       => 'datetime',
        'didox_added_at'        => 'datetime',
        'didox_contract_done_at'=> 'datetime',
        'payment_requested_at'  => 'datetime',
        'mygov_password'        => 'encrypted',
        'is_urgent'             => 'boolean',
        'is_didox'              => 'boolean',
        'urgent_accepted_at'    => 'datetime',
        'ready_sms_sent_at'     => 'datetime',
        'debt_called'           => 'boolean',
        'debt_called_at'        => 'datetime',
        'work_checklist'        => 'array',
    ];

    // Mijozlar qarzlari — oxirgi marta kim telefon qilgani
    public function debtCalledBy()
    {
        return $this->belongsTo(User::class, 'debt_called_by');
    }

    protected static function booted(): void
    {
        static::creating(function ($project) {
            if (empty($project->number)) {
                $project->number = '#' . str_pad(random_int(1, 999999999), 9, '0', STR_PAD_LEFT);
            }

            // Ketma-ket tartib raqami (№) — hisoblagichdan, hech qachon takrorlanmaydi.
            // O'chirilса ham hisoblagich orqaga qaytmaydi.
            if (empty($project->seq_no)) {
                $project->seq_no = \Illuminate\Support\Facades\DB::transaction(function () {
                    $row  = \Illuminate\Support\Facades\DB::table('counters')
                        ->where('name', 'project_seq')->lockForUpdate()->first();
                    $next = (int) ($row->value ?? 0) + 1;
                    \Illuminate\Support\Facades\DB::table('counters')
                        ->updateOrInsert(['name' => 'project_seq'], ['value' => $next]);
                    return $next;
                });
            }
        });

        static::updated(function ($project) {
            // Status o'zgarganda — mos xizmat timerini ishga tushirish
            if (!$project->wasChanged('status')) return;

            $newStatus = $project->status;
            $prevStatus = $project->getOriginal('status');
            $now       = now();

            // ── AVTOMATIK SMS: loyiha "Tugallangan" bo'limiga o'tsa, egasiga xabar ──
            // Faqat bir marta muvaffaqiyatli yuboriladi (qayta pul yechilmasin).
            // Oldin ketmagan bo'lsa (null / 'failed') — qayta urinadi.
            if ($newStatus === 'tugallangan' && $project->ready_sms_status !== 'sent') {
                $project->sendReadySms();
            }

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

        // ── Faoliyat jurnali: kim, qachon, nimani o'zgartirdi ──
        // Menejerlar endi o'z akkountida ishlagani uchun kerak (avtomatik
        // ichki yangilanishlar — saveQuietly() — bu yerga umuman kirmaydi).
        static::updated(function ($project) {
            if (!auth()->check()) return;

            $watched = [
                'owner_name'       => "Mijoz F.I.Sh",
                'address'          => 'Manzil',
                'status'           => 'Status',
                'category'         => 'Turi',
                'deadline_date'    => 'Muddat',
                'assigned_user_id' => "Mas'ul xodim",
            ];

            $lines = [];
            foreach ($watched as $field => $label) {
                if (!$project->wasChanged($field)) continue;

                $old = $project->getOriginal($field);
                $new = $project->{$field};

                $lines[] = match ($field) {
                    'status'   => "{$label}: " . (self::statusOptions()[$old] ?? ($old ?: '—')) . ' → ' . (self::statusOptions()[$new] ?? ($new ?: '—')),
                    'category' => "{$label}: " . (self::categoryOptions()[$old] ?? ($old ?: '—')) . ' → ' . (self::categoryOptions()[$new] ?? ($new ?: '—')),
                    'assigned_user_id' => "{$label}: " . ($old ? (User::find($old)?->name ?? '—') : '—') . ' → ' . ($new ? (User::find($new)?->name ?? '—') : '—'),
                    'deadline_date'    => "{$label}: " . ($old ? \Illuminate\Support\Carbon::parse($old)->format('d.m.Y') : '—') . ' → ' . ($new ? \Illuminate\Support\Carbon::parse($new)->format('d.m.Y') : '—'),
                    default    => "{$label}: " . ($old !== null && $old !== '' ? $old : '—') . ' → ' . ($new !== null && $new !== '' ? $new : '—'),
                };
            }

            if ($lines) {
                ActivityLog::create([
                    'user_id'        => auth()->id(),
                    'project_id'     => $project->id,
                    'project_number' => $project->number,
                    'action'         => 'project_edited',
                    'description'    => implode('; ', $lines),
                ]);
            }
        });

        static::deleted(function ($project) {
            if (!auth()->check() || $project->isForceDeleting()) return;

            ActivityLog::create([
                'user_id'        => auth()->id(),
                'project_id'     => $project->id,
                'project_number' => $project->number,
                'action'         => 'project_deleted',
                'description'    => "«{$project->owner_name}» — {$project->address}",
            ]);
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

    public function didoxAddedBy()
    {
        return $this->belongsTo(User::class, 'didox_added_by');
    }

    public function didoxContractDoneBy()
    {
        return $this->belongsTo(User::class, 'didox_contract_done_by');
    }

    public function invoiceSentBy()
    {
        return $this->belongsTo(User::class, 'invoice_sent_by');
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

    // Loyihaning bo'lim tarixiga yozuv qo'shadi (joriy ochiq yozuvni yopib,
    // yangisini ochadi). KanbanBoard va PaymentModal komponentlari birgalikda
    // ishlatadi — shu sababli model darajasida (ikkalasida ham qayta yozilmasin).
    public static function logStatusChange(self $project, string $newStatus, int $allocDays = 0, ?int $assignedUserId = null): void
    {
        \App\Models\ProjectStatusLog::where('project_id', $project->id)
            ->whereNull('left_at')
            ->update(['left_at' => now()]);

        \App\Models\ProjectStatusLog::create([
            'project_id'       => $project->id,
            'status'           => $newStatus,
            'entered_at'       => now(),
            'allocated_days'   => $allocDays,
            'assigned_user_id' => $assignedUserId ?? $project->assigned_user_id,
        ]);
    }

    /**
     * Egasiga "loyiha tayyor" SMS yuboradi va natijani o'zida saqlaydi.
     * updateQuietly ishlatiladi — "updated" hodisasi qayta yonmasligi uchun.
     * Natija $pendingSmsNotifications ga qo'shiladi (ekranda toast chiqishi uchun).
     */
    public function sendReadySms(): void
    {
        // Qulf: bir marta muvaffaqiyatli ketgan bo'lsa — qayta yubormaymiz (pul yechilmasin)
        if ($this->ready_sms_status === 'sent') return;

        // Egasining birinchi telefoni
        $phone = '';
        foreach ((array) $this->phones as $row) {
            $p = is_array($row) ? ($row['phone'] ?? '') : (string) $row;
            $p = trim($p);
            if ($p !== '' && $p !== '+998') { $phone = $p; break; }
        }

        if ($phone === '') {
            $this->forceFill([
                'ready_sms_status' => 'failed',
                'ready_sms_error'  => 'Telefon raqam kiritilmagan',
            ])->saveQuietly();
            self::$pendingSmsNotifications[] = [
                'ok'      => false,
                'message' => "«{$this->owner_name}» — SMS ketmadi: telefon raqam yo'q",
            ];
            return;
        }

        $text   = config('services.eskiz.ready_message');
        $result = \App\Services\EskizSms::send($phone, $text);

        if ($result['ok']) {
            $this->forceFill([
                'ready_sms_status' => 'sent',
                'ready_sms_sent_at'=> now(),
                'ready_sms_error'  => null,
            ])->saveQuietly();
            self::$pendingSmsNotifications[] = [
                'ok'      => true,
                'message' => "📱 «{$this->owner_name}» egasiga SMS yuborildi",
            ];
        } else {
            $this->forceFill([
                'ready_sms_status' => 'failed',
                'ready_sms_error'  => mb_substr($result['message'], 0, 250),
            ])->saveQuietly();
            self::$pendingSmsNotifications[] = [
                'ok'      => false,
                'message' => "❌ «{$this->owner_name}» — SMS ketmadi: {$result['message']}",
            ];
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

    // Ish holati (work progress) — Kanban statusidan mustaqil, rangli
    public static function workStatusOptions(): array
    {
        return [
            'yangi'           => ['label' => 'Yangi',            'color' => '#3b82f6'],  // ko'k
            'jarayonda'       => ['label' => 'Jarayonda',        'color' => '#f59e0b'],  // sariq
            'rad_qilindi'     => ['label' => 'Rad qilindi',      'color' => '#ef4444'],  // qizil
            'tayyor'          => ['label' => 'Tayyor',           'color' => '#22c55e'],  // yashil
            'tolov_jarayonda' => ['label' => "To'lov jarayonda", 'color' => '#8b5cf6'],  // binafsha
            'kelishildi'      => ['label' => 'Kelishildi',       'color' => '#14b8a6'],  // moviy-yashil
            'kelishilmadi'    => ['label' => 'Kelishilmadi',     'color' => '#f97316'],  // to'q sariq
        ];
    }

    // Vaqtincha to'xtatilgan ("o'lik") loyihalarni statistikadan chiqarib
    // tashlash uchun — Dashboard, Oylik hisobot, Buxgalteriya va shu kabi
    // umumiy hisobotlarda qo'llanadi. Kanban board va tahrirlash oynasi
    // buni ATAYLAB qo'llamaydi — u yerda to'xtatilgan loyiha ham ko'rinishi
    // va boshqarilishi kerak.
    public function scopeExcludePaused($query)
    {
        return $query->whereNull('timer_paused_at');
    }

    // Ro'yxatlarda mijoz FISH'i bir xil ko'rinishi uchun (<x-fish> komponenti):
    // hammasi katta harfda; familiya va ism TO'LIQ, otasining ismi ko'rsatilmaydi
    // (to'liq FISH — tooltip'da).
    public const FISH_WORDS = 2;

    public static function fishFull(?string $name): string
    {
        return mb_strtoupper(trim(preg_replace('/\s+/u', ' ', (string) $name)));
    }

    public static function fishShort(?string $name): string
    {
        $full = self::fishFull($name);
        return $full === '' ? '—' : implode(' ', array_slice(explode(' ', $full), 0, self::FISH_WORDS));
    }

    // Loyihalar ro'yxatidagi umumiy holat: tayyor / jarayonda / to'xtatilgan.
    // Ariza, Toposyomka, Eskiz loyiha va h.k. — hammasi "jarayonda".
    public const PROGRESS_TAYYOR_STATUSES      = ['tugallangan', 'didox', 'taqdim_etilgan'];
    public const PROGRESS_TOXTATILGAN_STATUSES = ['vaxtincha_toxtatilgan', 'bekor_qilingan'];

    public static function progressGroup(?string $status): string
    {
        return match (true) {
            in_array($status, self::PROGRESS_TAYYOR_STATUSES, true)      => 'tayyor',
            in_array($status, self::PROGRESS_TOXTATILGAN_STATUSES, true) => 'toxtatilgan',
            default                                                      => 'jarayonda',
        };
    }

    // Ro'yxat filtri uchun — progressGroup() bilan bir xil qoida, SQL ko'rinishida
    public function scopeProgressGroup($query, string $group)
    {
        return match ($group) {
            'tayyor'      => $query->whereIn('status', self::PROGRESS_TAYYOR_STATUSES),
            'toxtatilgan' => $query->whereIn('status', self::PROGRESS_TOXTATILGAN_STATUSES),
            default       => $query->whereNotIn('status', [...self::PROGRESS_TAYYOR_STATUSES, ...self::PROGRESS_TOXTATILGAN_STATUSES]),
        };
    }

    public static function progressGroupOptions(): array
    {
        return [
            // Yorliq fonlari (matn oq) — color → color2 orasida sekin oqib turadi
            'tayyor'      => ['label' => 'Tayyor',        'color' => '#16a34a', 'color2' => '#4ade80'], // yashil
            'jarayonda'   => ['label' => 'Jarayonda',     'color' => '#ffc400', 'color2' => '#ffe600', 'text' => '#111827'], // yorqin sariq, matn qora
            'toxtatilgan' => ['label' => "To'xtatilgan",  'color' => '#dc2626', 'color2' => '#f87171'], // qizil
        ];
    }

    // Kanban / Loyihalar ro'yxati / chap panel oy filtri: shu oyda OCHILGAN loyiha
    // yoki shu oyga tegishli xizmati bor loyiha (masalan iyundagi loyihaga oktabrda
    // Ariza qo'shilsa — ham iyunda, ham oktabrda ko'rinadi). Index'dan foydalanadi.
    public function scopeVisibleInMonth($query, int $year, int $month)
    {
        $start = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        return $query->where(fn ($q) => $q
            ->whereBetween('projects.created_at', [$start, $start->copy()->endOfMonth()])
            ->orWhereHas('services', fn ($s) => $s->inWorkMonth($year, $month)));
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

    // Ariza (Qabul arizasi) chop etishda — "Arizachi turi" va "Obyekt hududi"
    public static function applicantTypeOptions(): array
    {
        return [
            'mulk_egasi'     => 'Mulk egasi',
            'vakolatli_shaxs'=> 'Vakolatli shaxs (ishonchnoma bo\'yicha)',
            'meros_oluvchi'  => 'Meros oluvchi',
            'boshqa'         => 'Boshqa',
        ];
    }

    public static function regionOptions(): array
    {
        return [
            'toshkent_shahri'   => 'Toshkent shahri',
            'toshkent_viloyati' => 'Toshkent viloyati',
            'andijon'           => 'Andijon viloyati',
            'buxoro'            => 'Buxoro viloyati',
            'fargona'           => "Farg'ona viloyati",
            'jizzax'            => 'Jizzax viloyati',
            'xorazm'            => 'Xorazm viloyati',
            'namangan'          => 'Namangan viloyati',
            'navoiy'            => 'Navoiy viloyati',
            'qashqadaryo'       => 'Qashqadaryo viloyati',
            'qoraqalpogiston'   => "Qoraqalpog'iston Respublikasi",
            'samarqand'         => 'Samarqand viloyati',
            'sirdaryo'          => 'Sirdaryo viloyati',
            'surxondaryo'       => 'Surxondaryo viloyati',
        ];
    }
}
