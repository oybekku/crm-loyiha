<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('given_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');                          // Jihoz nomi
            $table->integer('quantity')->default(1);         // Miqdori
            $table->decimal('price', 15, 2)->default(0);     // Bir dona narxi
            $table->string('status')->default('berilgan');   // berilgan/qaytarilgan/yaroqsiz/yoqolgan
            $table->date('given_at')->nullable();            // Berilgan sana
            $table->date('returned_at')->nullable();         // Qaytarilgan sana
            $table->text('note')->nullable();                // Izoh
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_inventories');
    }
};
