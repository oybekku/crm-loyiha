<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Allaqachon bor bo'lsa — hech narsa qilmaymiz
        if (DB::table('project_statuses')->where('key', 'qayta_qongiroq')->exists()) {
            return;
        }

        $yangiLoyihalar = DB::table('project_statuses')->where('key', 'yangi_loyihalar')->first();

        if ($yangiLoyihalar) {
            // "Yangi loyihalar"dan keyingi barcha ustunlarni bir pog'ona pastga suramiz.
            // Yangi qator "Yangi loyihalar" bilan bir xil sort_order oladi, id kattaligi
            // tufayli (tartib: sort_order, id) aynan uning ortidan turadi.
            DB::table('project_statuses')
                ->where('sort_order', '>=', $yangiLoyihalar->sort_order)
                ->where('id', '!=', $yangiLoyihalar->id)
                ->increment('sort_order');
            $order = $yangiLoyihalar->sort_order;
        } else {
            $order = (int) DB::table('project_statuses')->max('sort_order') + 1;
        }

        DB::table('project_statuses')->insert([
            'key'        => 'qayta_qongiroq',
            'label'      => "Qayta qo'ng'iroq",
            'color'      => '#f97316',
            'sort_order' => $order,
            'is_archive' => 0,
            'is_hidden'  => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::forget('project_statuses_ordered');
    }

    public function down(): void
    {
        DB::table('project_statuses')->where('key', 'qayta_qongiroq')->delete();
        Cache::forget('project_statuses_ordered');
    }
};
