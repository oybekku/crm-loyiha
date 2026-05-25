<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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
    ];

    public function projects()
    {
        return $this->hasMany(Project::class, 'assigned_user_id');
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

    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isMenejer(): bool    { return $this->role === 'menejer'; }
    public function isHisobchi(): bool   { return $this->role === 'hisobchi'; }
    public function isBarjaruvchi(): bool { return $this->role === 'bajaruvchi'; }
    public function canSeeAllProjects(): bool { return in_array($this->role, ['admin', 'menejer']); }

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
            'password' => 'hashed',
        ];
    }
}
