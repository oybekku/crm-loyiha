<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Barcha foydalanuvchilar uchun umumiy sozlamalar (kalit → JSON qiymat). */
class AppSetting extends Model
{
    protected $primaryKey = 'key';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = ['key', 'value', 'updated_by'];
    protected $casts    = ['value' => 'array'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::find($key)?->value ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'updated_by' => auth()->id()]);
    }
}
