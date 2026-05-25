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
        Schema::create('project_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->enum('service_name', [
                'toposyomka', 'geologiya', 'eskiz_loyiha', 'texnik_korik',
                'ariza', 'konstruksiya', 'arxitektura', 'smeta',
                'ichki_dizayn', 'tashqi_dizayn', 'mualliflik_nazorati',
                'laboratoriya', 'kadastr', 'maxsus_xizmat',
            ]);
            $table->decimal('price', 15, 2)->default(0);
            $table->enum('discount_type', ['none', 'percent', 'fixed'])->default('none');
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->decimal('final_price', 15, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_services');
    }
};
