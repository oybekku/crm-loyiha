<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_accounts', function (Blueprint $table) {
            $table->boolean('is_commission_source')->default(false)->after('is_expense_account');
        });

        Schema::table('account_transfers', function (Blueprint $table) {
            $table->string('month', 7)->nullable()->after('comment');
        });
    }

    public function down(): void
    {
        Schema::table('financial_accounts', function (Blueprint $table) {
            $table->dropColumn('is_commission_source');
        });

        Schema::table('account_transfers', function (Blueprint $table) {
            $table->dropColumn('month');
        });
    }
};
