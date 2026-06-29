<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) "Yashirish" maydoni — yoqilsa, bo'lim Kanban va menyudan yashiriladi
        //    (arxivdan farqi: admin uchun ham ko'rinmaydi).
        Schema::table('project_statuses', function (Blueprint $table) {
            $table->boolean('is_hidden')->default(false)->after('is_archive');
        });

        // 2) "To'langan" bo'limini yashiramiz (kerak emas)
        DB::table('project_statuses')->where('key', 'tolangan')->update(['is_hidden' => true]);

        // 3) "To'lov jarayonida" ni "Yangi Eskiz loyiha" dan keyin qo'yamiz
        $yel = DB::table('project_statuses')->where('key', 'yangi_eskiz_loyiha')->value('sort_order');
        if ($yel !== null) {
            DB::table('project_statuses')->where('key', 'tolov_jarayonida')->update(['sort_order' => $yel + 1]);
        }

        // Kanban ustunlari keshini tozalaymiz
        DB::table('cache')->where('key', 'like', '%project_statuses_ordered%')->delete();
    }

    public function down(): void
    {
        DB::table('project_statuses')->where('key', 'tolangan')->update(['is_hidden' => false]);

        Schema::table('project_statuses', function (Blueprint $table) {
            $table->dropColumn('is_hidden');
        });
    }
};
