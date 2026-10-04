<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryPayment extends Model
{
    // Maosh turi: ishbay — toposyomka/ariza/eskiz ishlaridan (komissiya);
    // ma'muriy — direktor, admin, buxgalter... (firma daromadidan, ishga bog'liq emas)
    public const TYPE_ISHBAY  = 'ishbay';
    public const TYPE_MAMURIY = 'mamuriy';

    protected $fillable = ['user_id', 'month', 'amount', 'salary_type', 'paid_at', 'note', 'given_by'];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'date',
    ];

    public function user()   { return $this->belongsTo(User::class); }
    public function giver()  { return $this->belongsTo(User::class, 'given_by'); }

    public static function typeOptions(): array
    {
        return [self::TYPE_ISHBAY => 'Ishbay oylik', self::TYPE_MAMURIY => "Ma'muriy oylik"];
    }

    /** Xodimning standart maosh turi (bo'sh = ishbay) */
    public static function typeForUser(?User $user): string
    {
        return $user?->salary_type === self::TYPE_MAMURIY ? self::TYPE_MAMURIY : self::TYPE_ISHBAY;
    }

    /** To'lov turi: to'lovning o'zida yozilgani, bo'lmasa (eski to'lov) — xodimniki */
    public function getTypeAttribute(): string
    {
        return $this->salary_type ?: self::typeForUser($this->user);
    }
}
