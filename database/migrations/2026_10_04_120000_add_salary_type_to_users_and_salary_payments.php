<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Maosh turi: 'ishbay' (toposyomka/ariza/eskiz — ishdan komissiya) yoki
        // 'mamuriy' (direktor, admin, buxgalter... — firma daromadidan).
        // Bo'sh = ishbay. Eski to'lovlarda bo'sh qoladi — ular uchun xodimning
        // turi olinadi, ma'lumot o'zgartirilmaydi.
        Schema::table('users', function (Blueprint $table) {
            $table->string('salary_type', 20)->nullable()->after('base_salary');
        });
        Schema::table('employee_salary_payments', function (Blueprint $table) {
            $table->string('salary_type', 20)->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('salary_type'));
        Schema::table('employee_salary_payments', fn (Blueprint $table) => $table->dropColumn('salary_type'));
    }
};
