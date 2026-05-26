<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['key' => 'yangi',            'label' => 'Yangi',             'color' => '#3b82f6', 'sort_order' => 1,  'is_archive' => false],
            ['key' => 'tolov_jarayonida', 'label' => "To'lov jarayonida", 'color' => '#f59e0b', 'sort_order' => 2,  'is_archive' => false],
            ['key' => 'eskiz_loyiha',     'label' => 'Eskiz loyiha',      'color' => '#8b5cf6', 'sort_order' => 3,  'is_archive' => false],
            ['key' => 'tekshirish',       'label' => 'Tekshirish',        'color' => '#6366f1', 'sort_order' => 4,  'is_archive' => false],
            ['key' => 'tolangan',         'label' => "To'langan",         'color' => '#10b981', 'sort_order' => 5,  'is_archive' => false],
            ['key' => 'tugallangan',      'label' => 'Tugallangan',       'color' => '#6b7280', 'sort_order' => 6,  'is_archive' => true],
            ['key' => 'taqdim_etilgan',   'label' => 'Taqdim etilgan',    'color' => '#0ea5e9', 'sort_order' => 7,  'is_archive' => true],
            ['key' => 'bekor_qilingan',   'label' => 'Bekor qilingan',    'color' => '#ef4444', 'sort_order' => 8,  'is_archive' => true],
        ];

        foreach ($statuses as $status) {
            \App\Models\ProjectStatus::updateOrCreate(['key' => $status['key']], $status);
        }
    }
}
