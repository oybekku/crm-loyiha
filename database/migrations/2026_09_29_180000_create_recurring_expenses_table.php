<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Doimiy (har oylik) to'lovlar ro'yxati — arenda, svet, wi-fi...
        // Bu faqat shablon: pul chiqimi har oy qo'lda tasdiqlanib oddiy
        // `expenses` qatori sifatida yoziladi (recurring_expense_id bilan).
        Schema::create('recurring_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('amount', 15, 2)->default(0);
            $table->boolean('is_fixed')->default(true);   // aniq summa (arenda) yoki taxminiy (svet)
            $table->foreignId('account_id')->nullable()->constrained('financial_accounts')->nullOnDelete();
            $table->unsignedTinyInteger('due_day')->nullable(); // har oyning nechanchi sanasigacha
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('recurring_expense_id')->nullable()->after('category')
                ->constrained('recurring_expenses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recurring_expense_id');
        });
        Schema::dropIfExists('recurring_expenses');
    }
};
