<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // MyGOV: arizani kuzatish (my.gov.uz/uz/task-view) — ariza raqami + tekshirish paroli
            if (!Schema::hasColumn('projects', 'mygov_ariza_no')) {
                $table->string('mygov_ariza_no', 50)->nullable()->after('mygov_fish');
            }
            if (!Schema::hasColumn('projects', 'mygov_ariza_parol')) {
                $table->text('mygov_ariza_parol')->nullable()->after('mygov_ariza_no'); // encrypted cast
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['mygov_ariza_no', 'mygov_ariza_parol']);
        });
    }
};
