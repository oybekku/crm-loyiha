<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Loyiha narxi o'zgarishi — PriceChangeRecorder yozadi, "Narx o'zgarishlari" sahifasi ko'rsatadi. */
class PriceChange extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'project_id', 'project_number', 'owner_name', 'service_name',
        'kind', 'old_price', 'new_price', 'old_total', 'new_total',
    ];

    protected $casts = [
        'old_price'  => 'float',
        'new_price'  => 'float',
        'old_total'  => 'float',
        'new_total'  => 'float',
        'created_at' => 'datetime',
    ];

    public const KINDS = [
        'changed' => "Narx o'zgardi",
        'added'   => "Xizmat qo'shildi",
        'removed' => 'Xizmat olib tashlandi',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function delta(): float
    {
        return $this->new_price - $this->old_price;
    }

    public function serviceLabel(): string
    {
        return Project::serviceOptions()[$this->service_name] ?? (string) $this->service_name;
    }
}
