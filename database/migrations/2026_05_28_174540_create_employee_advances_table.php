<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('given_by')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('month', 7); // Y-m format: 2026-05
            $table->string('note')->nullable();
            $table->timestamp('given_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_advances');
    }
};
