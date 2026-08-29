<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Ish haqi to'lovidan (EmployeeSalaryPayment) avtomatik yozilgan
            // xarajat qatorini o'sha to'lov bilan bog'lab turadi — to'lov
            // tahrirlansa/o'chirilsa, shu xarajat ham sinxron yangilanadi/
            // o'chiriladi.
            $table->unsignedBigInteger('salary_payment_id')->nullable()->after('user_id');
            $table->index('salary_payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['salary_payment_id']);
            $table->dropColumn('salary_payment_id');
        });
    }
};
