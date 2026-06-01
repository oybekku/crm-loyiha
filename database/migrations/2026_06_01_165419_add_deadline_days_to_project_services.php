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
        Schema::table('project_services', function (Blueprint $table) {
            $table->unsignedSmallInteger('deadline_days')->nullable()->after('assigned_user_id');
            $table->timestamp('work_started_at')->nullable()->after('deadline_days');
        });
    }

    public function down(): void
    {
        Schema::table('project_services', function (Blueprint $table) {
            $table->dropColumn(['deadline_days', 'work_started_at']);
        });
    }
};
