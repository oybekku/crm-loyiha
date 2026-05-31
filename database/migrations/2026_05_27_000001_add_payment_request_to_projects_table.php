<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->timestamp('payment_requested_at')->nullable()->after('status');
            $table->foreignId('payment_requested_by')->nullable()->constrained('users')->nullOnDelete()->after('payment_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['payment_requested_by']);
            $table->dropColumn(['payment_requested_at', 'payment_requested_by']);
        });
    }
};
