<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_accounts', function (Blueprint $table) {
            // Hisob egasi (xodim) — masalan menejerga xarajatlar uchun berilgan
            // shaxsiy karta. "Yangi bux"da chiqim Mas'uli shu xodim bo'lsa,
            // uning kartasi avtomatik tanlanadi. Bo'sh = kompaniya hisobi.
            $table->unsignedBigInteger('user_id')->nullable()->after('type');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('financial_accounts', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
