<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pul qaysi oy hamyonidan berilgani (masalan sentabr oyligi — oktabr pulidan).
        // NULL — maosh oyi bilan bir xil (avvalgidek). Xodim qarzi baribir `month` bo'yicha yopiladi.
        Schema::table('employee_salary_payments', function (Blueprint $table) {
            $table->string('wallet_month', 7)->nullable()->after('month');
        });
    }

    public function down(): void
    {
        Schema::table('employee_salary_payments', function (Blueprint $table) {
            $table->dropColumn('wallet_month');
        });
    }
};
