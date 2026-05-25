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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('owner_name');
            $table->string('title')->nullable();
            $table->string('address');
            $table->json('phones');
            $table->text('description')->nullable();
            $table->enum('category', ['turar', 'tijorat', 'qishloq', 'sanoat', 'boshqa'])->default('turar');
            $table->enum('status', [
                'yangi',
                'tolov_jarayonida',
                'tekshirish',
                'tolangan',
                'tugallangan',
                'taqdim_etilgan',
                'bekor_qilingan',
            ])->default('yangi');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('total_price', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
