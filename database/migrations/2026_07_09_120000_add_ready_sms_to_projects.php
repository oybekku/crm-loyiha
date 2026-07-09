<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // "Loyiha tayyor" SMS holati:
            //   null      — hali yuborilmagan
            //   'sent'    — muvaffaqiyatli ketdi
            //   'failed'  — ketmadi (kartada belgi chiqadi, keyin qayta urinish mumkin)
            $table->string('ready_sms_status', 20)->nullable()->after('urgent_accepted_by');
            $table->timestamp('ready_sms_sent_at')->nullable()->after('ready_sms_status');
            $table->string('ready_sms_error', 255)->nullable()->after('ready_sms_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['ready_sms_status', 'ready_sms_sent_at', 'ready_sms_error']);
        });
    }
};
