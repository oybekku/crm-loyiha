<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['project_id', 'user_id']);
        });

        // Mavjud assigned_user_id ma'lumotlarini pivot jadvaliga ko'chirish
        DB::statement("
            INSERT IGNORE INTO project_user (project_id, user_id, created_at, updated_at)
            SELECT id, assigned_user_id, NOW(), NOW()
            FROM projects
            WHERE assigned_user_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('project_user');
    }
};
