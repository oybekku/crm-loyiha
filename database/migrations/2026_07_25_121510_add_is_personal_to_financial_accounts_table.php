<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('financial_accounts', function (Blueprint $table) {
            // Shaxsiy/xarajat hisobi (masalan xodimga o'tkazilgan pul) — bunday
            // hisoblar "Jami balans"ga qo'shilmaydi, faqat o'z alohida balansini
            // ko'rsatadi (haqiqiy kompaniya hamyonlari bilan aralashmasin).
            $table->boolean('is_personal')->default(false)->after('is_favorite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_accounts', function (Blueprint $table) {
            $table->dropColumn('is_personal');
        });
    }
};
