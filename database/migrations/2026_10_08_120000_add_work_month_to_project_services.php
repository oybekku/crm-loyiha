<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_services', function (Blueprint $table) {
            // Xizmatning "ish oyi" ('Y-m') — oyliklar, Oylik hisobot va boshqa oylik
            // hisob-kitoblar shu bo'yicha. Odatda loyiha ochilgan oy; loyihaga
            // keyinroq qo'shilgan xizmat (masalan iyundagi loyihaga oktabrda Ariza)
            // qo'shilgan oyini oladi.
            $table->string('work_month', 7)->nullable()->index();
        });

        // Mavjud BARCHA xizmatlar — loyiha ochilgan oy (o'tgan oylarning hisob-
        // kitobi aynan o'zgarmasin; yangi qoida faqat bundan keyingi xizmatlarga).
        DB::statement("UPDATE project_services s JOIN projects p ON p.id = s.project_id
                       SET s.work_month = DATE_FORMAT(p.created_at, '%Y-%m')");
    }

    public function down(): void
    {
        Schema::table('project_services', fn (Blueprint $table) => $table->dropColumn('work_month'));
    }
};
