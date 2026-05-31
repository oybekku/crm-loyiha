<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'project_id', 'payment_id', 'user_id',
        'action', 'amount', 'old_amount', 'description',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'old_amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function actionLabel(): string
    {
        return match($this->action) {
            'created'           => "To'lov qo'shildi",
            'edited'            => "Summa o'zgartirildi",
            'employee_assigned' => "Hodim biriktirildi",
            default             => $this->action,
        };
    }
}
