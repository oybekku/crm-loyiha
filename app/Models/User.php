<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'permissions',
        'commission_rate',
    ];

    public static function defaultPermissions(): array
    {
        $kanbanKeys = [];
        try {
            $kanbanKeys = ProjectStatus::allOrdered()->map(fn($ps) => 'kanban_' . $ps->key)->toArray();
        } catch (\Throwable) {}

        return array_values(array_merge([
            'page_kanban_board',   // Loyihalar menyu
            'page_arxiv_page',     // Arxiv menyu
            'loyiha_tahrirlash',   // Loyihani tahrirlash
            'loyiha_otkazish',     // Loyihani bosqichga o'tkazish
            'tolov_kiritish',      // To'lov kiritish
            'loyiha_yuborish',     // Loyihani yuborish
        ], $kanbanKeys));
    }

    public static function allPermissions(): array
    {
        $permissions = [];

        // Sahifalar (Pages) — avtomatik topiladi
        foreach (glob(app_path('Filament/Pages/*.php')) ?: [] as $file) {
            $className = basename($file, '.php');
            $class = "App\\Filament\\Pages\\{$className}";
            if (!class_exists($class) || !method_exists($class, 'menuPermissionKey')) continue;
            $permissions[$class::menuPermissionKey()] = 'Menyu: ' . $class::menuPermissionLabel();
        }

        // Resurslar (Resources) — avtomatik topiladi
        foreach (glob(app_path('Filament/Resources/*Resource.php')) ?: [] as $file) {
            $className = basename($file, '.php');
            $class = "App\\Filament\\Resources\\{$className}";
            if (!class_exists($class) || !method_exists($class, 'menuPermissionKey')) continue;
            $permissions[$class::menuPermissionKey()] = 'Menyu: ' . $class::menuPermissionLabel();
        }

        // Amallar (qo'lda)
        $permissions += [
            'yangi_loyiha'      => "Amal: Yangi loyiha yaratish",
            'loyiha_tahrirlash' => "Amal: Loyihani tahrirlash",
            'tolov_kiritish'    => "Amal: To'lov kiritish",
            'loyiha_otkazish'   => "Amal: Loyihani bosqichga o'tkazish",
            'loyiha_yuborish'   => "Amal: Loyihani yuborish",
            'barcha_loyihalar'  => "Amal: Barcha loyihalarni ko'rish",
        ];

        // Kanban ustunlari — bazadan dinamik o'qiladi
        try {
            $dbStatuses = \App\Models\ProjectStatus::allOrdered();
            foreach ($dbStatuses as $ps) {
                $permissions['kanban_' . $ps->key] = "Kanban ustun: {$ps->label}";
            }
        } catch (\Throwable $e) {
            // Baza tayyor bo'lmagan holat uchun
        }

        return $permissions;
    }

    public function hasPermission(string $key): bool
    {
        if ($this->isAdmin()) return true;
        return in_array($key, $this->permissions ?? []);
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'assigned_user_id');
    }

    public function assignedProjects()
    {
        return $this->belongsToMany(Project::class, 'project_user')->withTimestamps();
    }

    public function inventories()
    {
        return $this->hasMany(EmployeeInventory::class);
    }

    public function getRoleNameAttribute(): string
    {
        return match($this->role) {
            'admin'      => 'Admin',
            'menejer'    => 'Menejer',
            'hisobchi'   => 'Hisobchi (Buxgalter)',
            'bajaruvchi' => 'Bajaruvchi (Hodim)',
            default      => $this->role,
        };
    }

    public function canAccessPanel(Panel $panel): bool { return true; }

    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isMenejer(): bool    { return $this->role === 'menejer'; }
    public function isHisobchi(): bool   { return $this->role === 'hisobchi'; }
    public function isBajaruvchi(): bool  { return $this->role === 'bajaruvchi'; }
    public function canSeeAllProjects(): bool { return in_array($this->role, ['admin', 'menejer']); }

    /**
     * Hodim (bajaruvchi) Kanbanда qaysi ustunlarni ko'radi — o'zi biriktirilgan
     * ish turlariga (toposyomka/eskiz) qarab. Bitta tur bo'lsa faqat o'sha bo'lim.
     * Hech qaysi ish biriktirilmagan bo'lsa — bo'sh (hech narsa ko'rinmaydi).
     */
    public function kanbanServiceCols(): array
    {
        static $cache = [];
        if (array_key_exists($this->id, $cache)) return $cache[$this->id];

        $types = \App\Models\ProjectService::where('assigned_user_id', $this->id)
            ->whereHas('project', fn ($q) => $q->whereNotIn('status', ['tugallangan', 'taqdim_etilgan', 'bekor_qilingan']))
            ->distinct()
            ->pluck('service_name')
            ->toArray();

        $cols = [];
        if (in_array('toposyomka', $types))   $cols = array_merge($cols, ['yangi_toposyomka', 'toposyomka']);
        if (in_array('eskiz_loyiha', $types)) $cols = array_merge($cols, ['yangi_eskiz_loyiha', 'eskiz_loyiha']);
        if (!empty($cols)) $cols[] = 'kechikayotgan';

        return $cache[$this->id] = $cols;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'permissions'       => 'array',
        ];
    }
}
