<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Umumiy "kim nima qildi" jurnali — to'lovlarга tegishli bo'lmagan muhim
 * amallar uchun (loyiha o'chirilishi, muhim maydonlar tahrirlanishi).
 * To'lov (summa) yaratilishi/tahrirlanishi/o'chirilishi PaymentLog'da
 * yoziladi — ikkalasi "Faoliyat jurnali" sahifasida birlashtirilib ko'rsatiladi.
 */
class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'project_id', 'project_number', 'action', 'description',
    ];

    protected $casts = [
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

    public function actionLabel(): string
    {
        return match ($this->action) {
            'project_deleted' => "Loyiha o'chirildi",
            'project_edited'  => 'Loyiha tahrirlandi',
            default           => $this->action,
        };
    }
}
