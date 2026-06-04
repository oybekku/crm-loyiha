<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Obloshka (muqova) uchun alohida manzil. Bo'sh bo'lsa oddiy manzil ishlatiladi.
            $table->text('oblozhka_address')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('oblozhka_address');
        });
    }
};
