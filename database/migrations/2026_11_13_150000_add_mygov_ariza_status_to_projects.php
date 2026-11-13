<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // my.gov.uz ariza holati — oxirgi qo'lda tekshirish natijasi
            if (!Schema::hasColumn('projects', 'mygov_ariza_url')) {
                $table->text('mygov_ariza_url')->nullable()->after('mygov_ariza_parol');      // natija sahifasi (himoya kodisiz ochiladi)
            }
            if (!Schema::hasColumn('projects', 'mygov_ariza_holat')) {
                $table->string('mygov_ariza_holat', 100)->nullable()->after('mygov_ariza_url');
            }
            if (!Schema::hasColumn('projects', 'mygov_ariza_info')) {
                $table->json('mygov_ariza_info')->nullable()->after('mygov_ariza_holat');     // sana, xizmat, bosqichlar…
            }
            if (!Schema::hasColumn('projects', 'mygov_ariza_checked_at')) {
                $table->timestamp('mygov_ariza_checked_at')->nullable()->after('mygov_ariza_info');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['mygov_ariza_url', 'mygov_ariza_holat', 'mygov_ariza_info', 'mygov_ariza_checked_at']);
        });
    }
};
