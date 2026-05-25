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
        Schema::table('projects', function (Blueprint $table) {
            $table->index('status');
            $table->index('assigned_user_id');
            $table->index('created_at');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('project_id');
            $table->index('payment_date');
        });

        Schema::table('project_status_logs', function (Blueprint $table) {
            $table->index(['project_id', 'left_at']);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['assigned_user_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['project_id']);
            $table->dropIndex(['payment_date']);
        });

        Schema::table('project_status_logs', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'left_at']);
        });
    }
};
