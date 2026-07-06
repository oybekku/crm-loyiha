<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Allaqachon bor bo'lsa — hech narsa qilmaymiz
        if (DB::table('project_statuses')->where('key', 'mygov')->exists()) {
            return;
        }

        $done = DB::table('project_statuses')->where('key', 'tugallangan')->first();

        if ($done) {
            // "Tugallangan"dan keyingi ustunlarni bir pog'ona pastga suramiz — joy ochamiz
            DB::table('project_statuses')
                ->where('sort_order', '>', $done->sort_order)
                ->increment('sort_order');
            $order = $done->sort_order + 1;
        } else {
            $order = (int) DB::table('project_statuses')->max('sort_order') + 1;
        }

        DB::table('project_statuses')->insert([
            'key'        => 'mygov',
            'label'      => 'MyGOV',
            'color'      => '#0d9488',
            'sort_order' => $order,
            'is_archive' => 0,
            'is_hidden'  => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('project_statuses')->where('key', 'mygov')->delete();
    }
};
