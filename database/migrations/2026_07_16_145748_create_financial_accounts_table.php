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
        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['karta', 'naqd', 'bank'])->default('karta');
            $table->string('name')->nullable();          // masalan "Asosiy karta"
            $table->string('card_number')->nullable();    // karta uchun
            $table->string('bank_name')->nullable();      // karta/bank uchun (Humo, Uzcard, ...)
            $table->string('expiry_date')->nullable();    // karta uchun, masalan "01/30"
            $table->string('account_number')->nullable();  // bank hisob raqami uchun
            $table->boolean('is_favorite')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_accounts');
    }
};
