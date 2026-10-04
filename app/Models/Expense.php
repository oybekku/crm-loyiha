<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'account_id', 'user_id', 'salary_payment_id', 'month', 'amount', 'comment', 'expense_date', 'created_by',
        'project_id', 'responsible_id', 'note', 'attachment', 'category', 'recurring_expense_id',
    ];

    public const KIND_OYLIK   = 'oylik';     // oylik / avans
    public const KIND_XARAJAT = 'xarajat';   // qolgan barcha chiqimlar

    public static function kindOptions(): array
    {
        return [self::KIND_XARAJAT => 'Xarajat', self::KIND_OYLIK => 'Oylik / avans'];
    }

    /**
     * Chiqim turi. category bo'sh bo'lgan eski qatorlarda — oylik tizimidan
     * (user_id) yozilgan bo'lsa "oylik", aks holda "xarajat". Ma'lumot o'zgarmaydi.
     */
    public function getKindAttribute(): string
    {
        return $this->category ?: ($this->user_id ? self::KIND_OYLIK : self::KIND_XARAJAT);
    }

    /** SQL'da ham xuddi getKindAttribute qoidasi */
    public function scopeOfKind($query, string $kind)
    {
        return $kind === self::KIND_OYLIK
            ? $query->where(fn ($q) => $q->where('category', self::KIND_OYLIK)
                ->orWhere(fn ($q2) => $q2->whereNull('category')->whereNotNull('user_id')))
            : $query->where(fn ($q) => $q->where('category', self::KIND_XARAJAT)
                ->orWhere(fn ($q2) => $q2->whereNull('category')->whereNull('user_id')));
    }

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(FinancialAccount::class, 'account_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function recurringExpense()
    {
        return $this->belongsTo(RecurringExpense::class);
    }

    public function salaryPayment()
    {
        return $this->belongsTo(EmployeeSalaryPayment::class, 'salary_payment_id');
    }

    /** Oylik qatori uchun maosh turi (ishbay | mamuriy): to'lovniki, bo'lmasa xodimniki */
    public function getSalaryTypeAttribute(): string
    {
        return $this->salaryPayment?->salary_type
            ?: EmployeeSalaryPayment::typeForUser($this->user ?? $this->responsible);
    }

    /**
     * Xodimga berilgan REAL ish haqi to'lovidan (EmployeeSalaryPayment)
     * avtomatik yozilgan xarajat qatormi — bunday qatorlar qo'lda
     * tahrirlanmaydi/o'chirilmaydi, chunki bog'liq to'lov o'zgartirilsa/
     * o'chirilsa shu qator ham avtomatik sinxron yangilanadi/o'chiriladi
     * (Oylik hisobot -> "To'lanishi kerak" jadvali orqali).
     */
    public function getIsAutoAttribute(): bool
    {
        return !is_null($this->user_id);
    }
}
