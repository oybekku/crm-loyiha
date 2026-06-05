<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Kun hisobi to'xtatilgan vaqt (kutish holati). NULL = hisob ishlayapti.
            $table->timestamp('timer_paused_at')->nullable()->after('deadline_date');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('timer_paused_at');
        });
    }
};
