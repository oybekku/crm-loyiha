<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_services', function (Blueprint $table) {
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete()->after('project_id');
        });
    }

    public function down(): void
    {
        Schema::table('project_services', function (Blueprint $table) {
            $table->dropForeign(['assigned_user_id']);
            $table->dropColumn('assigned_user_id');
        });
    }
};
