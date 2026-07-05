<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('work_status', ['yangi', 'jarayonda', 'rad_qilindi', 'tayyor', 'tolov_jarayonda'])
                  ->default('yangi')
                  ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('work_status');
        });
    }
};
