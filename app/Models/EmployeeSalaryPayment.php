<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryPayment extends Model
{
    // Maosh turi: ishbay — toposyomka/ariza/eskiz ishlaridan (komissiya);
    // ma'muriy — direktor, admin, buxgalter... (firma daromadidan, ishga bog'liq emas)
    public const TYPE_ISHBAY  = 'ishbay';
    public const TYPE_MAMURIY = 'mamuriy';

    // month — maosh qaysi oy uchun (qarz shu oyda yopiladi); wallet_month — pul qaysi oy
    // hamyonidan (Buxgalteriya xarajati shu oyga yoziladi; NULL — month bilan bir xil)
    protected $fillable = ['user_id', 'month', 'wallet_month', 'amount', 'salary_type', 'paid_at', 'note', 'given_by'];

    /** Pul qaysi oy hamyonidan berilgan */
    public function walletMonth(): string
    {
        return $this->wallet_month ?: $this->month;
    }

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'date',
    ];

    public function user()   { return $this->belongsTo(User::class); }
    public function giver()  { return $this->belongsTo(User::class, 'given_by'); }

    public static function typeOptions(): array
    {
        // Nom — pul NIMA UCHUN berilayotgani (kimga emas): direktor ham ishi uchun komissiya oladi
        return [self::TYPE_ISHBAY => 'Bajarilgan ishlar uchun', self::TYPE_MAMURIY => 'Oylik maosh (oklad)'];
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
