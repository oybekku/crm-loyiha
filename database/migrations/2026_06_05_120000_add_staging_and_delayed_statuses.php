<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\ProjectStatus;

return new class extends Migration
{
    public function up(): void
    {
        // Yangi 3 ta bo'lim + barcha statuslar tartibini to'g'rilash
        $statuses = [
            ['key' => 'yangi',              'label' => 'Ariza',                'color' => '#3b82f6', 'sort_order' => 1,  'is_archive' => false],
            ['key' => 'tolov_jarayonida',   'label' => "To'lov jarayonida",    'color' => '#f59e0b', 'sort_order' => 2,  'is_archive' => false],
            ['key' => 'yangi_loyihalar',    'label' => 'Yangi loyihalar',      'color' => '#22c55e', 'sort_order' => 3,  'is_archive' => false],
            ['key' => 'yangi_toposyomka',   'label' => 'Yangi Toposyomka',     'color' => '#a78bfa', 'sort_order' => 4,  'is_archive' => false],
            ['key' => 'toposyomka',         'label' => 'Toposyomka',           'color' => '#7c3aed', 'sort_order' => 5,  'is_archive' => false],
            ['key' => 'yangi_eskiz_loyiha', 'label' => 'Yangi Eskiz loyiha',   'color' => '#c4b5fd', 'sort_order' => 6,  'is_archive' => false],
            ['key' => 'eskiz_loyiha',       'label' => 'Eskiz loyiha',         'color' => '#8b5cf6', 'sort_order' => 7,  'is_archive' => false],
            ['key' => 'tekshirish',         'label' => 'Tekshirish',           'color' => '#6366f1', 'sort_order' => 8,  'is_archive' => false],
            ['key' => 'tolangan',           'label' => "To'langan",            'color' => '#10b981', 'sort_order' => 9,  'is_archive' => false],
            ['key' => 'kechikayotgan',      'label' => 'Kechikayotgan loyihalar','color' => '#ef4444', 'sort_order' => 10, 'is_archive' => false],
            ['key' => 'tugallangan',        'label' => 'Tugallangan',          'color' => '#6b7280', 'sort_order' => 11, 'is_archive' => true],
            ['key' => 'taqdim_etilgan',     'label' => 'Taqdim etilgan',       'color' => '#0ea5e9', 'sort_order' => 12, 'is_archive' => true],
            ['key' => 'bekor_qilingan',     'label' => 'Bekor qilingan',       'color' => '#ef4444', 'sort_order' => 13, 'is_archive' => true],
        ];

        foreach ($statuses as $s) {
            // label faqat yangi yaratilganda qo'yiladi — mavjudini buzmaymiz (faqat sort_order yangilanadi)
            $existing = ProjectStatus::where('key', $s['key'])->first();
            if ($existing) {
                $existing->update(['sort_order' => $s['sort_order']]);
            } else {
                ProjectStatus::create($s);
            }
        }
    }

    public function down(): void
    {
        ProjectStatus::whereIn('key', ['yangi_toposyomka', 'yangi_eskiz_loyiha', 'kechikayotgan'])->delete();
    }
};
