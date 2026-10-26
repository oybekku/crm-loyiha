<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Loyiha narxi o'zgarishlari (PriceChangeRecorder) — "Narx o'zgarishlari" sahifasi uchun
        Schema::create('price_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('project_number')->nullable(); // loyiha o'chirilsa ham o'qilishi uchun
            $table->string('owner_name')->nullable();
            $table->string('service_name')->nullable();
            $table->string('kind', 16); // changed | added | removed
            $table->decimal('old_price', 15, 2)->default(0);
            $table->decimal('new_price', 15, 2)->default(0);
            $table->decimal('old_total', 15, 2)->default(0);
            $table->decimal('new_total', 15, 2)->default(0);
            $table->timestamp('created_at')->useCurrent()->index();
        });

        // Filament "qo'ng'iroqcha" xabarlari (databaseNotifications)
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('price_changes');
        Schema::dropIfExists('notifications');
    }
};
