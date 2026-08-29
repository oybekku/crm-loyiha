<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ko'rinadigan lavozim (masalan "Direktor") — TIZIM huquqidan
            // (role: admin/menejer/hisobchi/bajaruvchi) mustaqil. Bo'sh
            // bo'lsa, "To'lanishi kerak" jadvalida role_name ko'rsatiladi.
            $table->string('position')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
