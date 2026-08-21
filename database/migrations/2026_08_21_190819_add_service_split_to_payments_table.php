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
        Schema::table('payments', function (Blueprint $table) {
            // Bir nechta xizmat tanlanganda ketma-ket (waterfall) taqsimlangan
            // aniq summalar: {"toposyomka": 3000000, "eskiz_loyiha": 1000000}.
            // NULL bo'lsa (eski to'lovlar) - EmployeePayableService eski
            // (narx nisbati bo'yicha proporsional) formulani ishlatishda davom etadi.
            $table->json('service_split')->nullable()->after('services');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('service_split');
        });
    }
};
