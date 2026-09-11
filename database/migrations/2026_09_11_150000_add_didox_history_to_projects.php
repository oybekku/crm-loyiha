<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // "Yangi Didox"ga qachon va kim tomonidan o'tkazilgani (menejer/admin)
            $table->timestamp('didox_added_at')->nullable()->after('is_didox');
            $table->unsignedBigInteger('didox_added_by')->nullable()->after('didox_added_at');
            // Hisobchi shartnomani qachon va kim tayyor deb belgilagani
            $table->timestamp('didox_contract_done_at')->nullable()->after('didox_added_by');
            $table->unsignedBigInteger('didox_contract_done_by')->nullable()->after('didox_contract_done_at');
            // Shot-fakturani kim yuborgani (invoice_sent_at allaqachon bor)
            $table->unsignedBigInteger('invoice_sent_by')->nullable()->after('invoice_sent_at');
        });

        // Eski (bu ustunlar qo'shilishidan oldingi) is_didox=true loyihalar uchun
        // "qo'shildi/tayyor" vaqtlarini mavjud project_status_logs'dan tiklaymiz —
        // faqat vaqt (kim bosgani eski yozuvlarda kuzatilmagan, shu sababli bo'sh qoladi).
        $projects = DB::table('projects')->where('is_didox', true)->get(['id']);
        foreach ($projects as $project) {
            $addedAt = DB::table('project_status_logs')
                ->where('project_id', $project->id)
                ->where('status', 'yangi_didox')
                ->min('entered_at');

            $doneAt = DB::table('project_status_logs')
                ->where('project_id', $project->id)
                ->where('status', 'yangi_didox')
                ->max('left_at');

            if ($addedAt || $doneAt) {
                DB::table('projects')->where('id', $project->id)->update([
                    'didox_added_at'         => $addedAt,
                    'didox_contract_done_at' => $doneAt,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'didox_added_at', 'didox_added_by',
                'didox_contract_done_at', 'didox_contract_done_by',
                'invoice_sent_by',
            ]);
        });
    }
};
