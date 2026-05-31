<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $eskiz = DB::table('project_statuses')->where('key', 'eskiz_loyiha')->first();
        if (!$eskiz) return;

        $insertAt = $eskiz->sort_order;

        DB::table('project_statuses')
            ->where('sort_order', '>=', $insertAt)
            ->increment('sort_order');

        DB::table('project_statuses')->insert([
            'key'        => 'toposyomka',
            'label'      => 'Toposyomka',
            'color'      => '#7c3aed',
            'sort_order' => $insertAt,
            'is_archive' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $topo = DB::table('project_statuses')->where('key', 'toposyomka')->first();
        if (!$topo) return;

        $removedAt = $topo->sort_order;
        DB::table('project_statuses')->where('key', 'toposyomka')->delete();

        DB::table('project_statuses')
            ->where('sort_order', '>', $removedAt)
            ->decrement('sort_order');
    }
};
