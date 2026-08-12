<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Loyiha bir marta "Yangi Didox"ga o'tkazilsa — doimiy belgi bo'lib qoladi
            // (keyinchalik boshqa bo'limlarga o'tsa ham o'chmaydi), shu bilan ish
            // yakunlanganda "bunga DIDOX shot-faktura kerak edi" degan narsa yo'qolib
            // qolmaydi.
            $table->boolean('is_didox')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('is_didox');
        });
    }
};
