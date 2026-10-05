<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Barcha foydalanuvchilar uchun UMUMIY sozlamalar (kalit → JSON qiymat).
        // Masalan: Yangi bux / Hisobotlar'da yashirilgan oylar — bir admin
        // yashirsa, hamma adminlarda bir xil ko'rinadi.
        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->json('value')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
