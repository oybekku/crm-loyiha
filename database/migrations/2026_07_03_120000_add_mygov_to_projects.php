<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('mygov_login')->nullable()->after('phones');
            $table->text('mygov_password')->nullable()->after('mygov_login');   // shifrlangan holda saqlanadi
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['mygov_login', 'mygov_password']);
        });
    }
};
