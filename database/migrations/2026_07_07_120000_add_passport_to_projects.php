<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Pasport ma'lumotlari (keyinchalik foydalanish uchun)
            $table->string('passport_series')->nullable()->after('phones');       // Masalan: AD 3824135
            $table->string('passport_issued_by')->nullable()->after('passport_series'); // Kim tomonidan berilgan
            $table->string('pinfl', 20)->nullable()->after('passport_issued_by'); // ПИНФЛ / JSHSHIR
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['passport_series', 'passport_issued_by', 'pinfl']);
        });
    }
};
