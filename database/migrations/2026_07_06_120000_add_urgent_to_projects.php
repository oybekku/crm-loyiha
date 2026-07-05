<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Zudlik bilan qilinsin — admin yoqadi, karta "yonadi"
            $table->boolean('is_urgent')->default(false)->after('work_status');
            // Mas'ul hodim "Qabul qildim" bosgan vaqti/kim
            $table->timestamp('urgent_accepted_at')->nullable()->after('is_urgent');
            $table->unsignedBigInteger('urgent_accepted_by')->nullable()->after('urgent_accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['is_urgent', 'urgent_accepted_at', 'urgent_accepted_by']);
        });
    }
};
