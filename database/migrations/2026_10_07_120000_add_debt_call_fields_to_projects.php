<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Yangi bux → Mijozlar qarzlari: qarzdor mijoz bilan ishlash uchun
            // izoh va "telefon qilindi" belgisi (oxirgi qo'ng'iroq sanasi/kim).
            $table->text('debt_comment')->nullable();
            $table->boolean('debt_called')->default(false);
            $table->timestamp('debt_called_at')->nullable();
            $table->unsignedBigInteger('debt_called_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('projects', fn (Blueprint $table) => $table->dropColumn([
            'debt_comment', 'debt_called', 'debt_called_at', 'debt_called_by',
        ]));
    }
};
