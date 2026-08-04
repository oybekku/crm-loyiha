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
        Schema::create('user_commission_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('rate', 5, 2);
            // 'Y-m' (masalan '2026-08') — shu oydan boshlab shu foiz qo'llanadi.
            // Undan oldingi oylar users.commission_rate (yoki oldingi eng yaqin
            // yozuv) bo'yicha hisoblanaveradi — eski hisobotlar o'zgarmaydi.
            $table->string('effective_month', 7);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'effective_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_commission_rates');
    }
};
