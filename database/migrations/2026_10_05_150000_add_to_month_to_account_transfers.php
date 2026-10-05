<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_transfers', function (Blueprint $table) {
            // Oydan oyga o'tkazma: pul `month` oyidan chiqadi, `to_month` oyiga kiradi
            // (Yangi bux — oy pullari). Bo'sh = o'sha oyning o'zi (eski o'tkazmalar o'zgarmaydi).
            $table->string('to_month', 7)->nullable()->after('month');
        });
    }

    public function down(): void
    {
        Schema::table('account_transfers', fn (Blueprint $table) => $table->dropColumn('to_month'));
    }
};
