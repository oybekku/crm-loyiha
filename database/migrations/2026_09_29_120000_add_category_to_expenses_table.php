<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Chiqim turi: 'oylik' (oylik/avans) yoki 'xarajat'. Eski qatorlarda
            // bo'sh qoladi — ular uchun Expense::kind oylik tizimidan yozilganmi
            // (user_id) degan belgiga qarab aniqlanadi, ma'lumot o'zgartirilmaydi.
            $table->string('category', 20)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
