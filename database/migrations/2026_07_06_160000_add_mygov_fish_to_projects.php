<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // MyGOV: ariza kim orqali kelgani (FISH) — statistika uchun ochiq matn
            $table->string('mygov_fish')->nullable()->after('mygov_password');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('mygov_fish');
        });
    }
};
