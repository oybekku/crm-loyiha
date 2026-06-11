<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('project_services', function (Blueprint $table) {
            // Ish tekshirishga yuborilgan vaqt — muddat shu vaqtda "muzlaydi"
            $table->timestamp('submitted_at')->nullable()->after('work_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('project_services', function (Blueprint $table) {
            $table->dropColumn('submitted_at');
        });
    }
};
