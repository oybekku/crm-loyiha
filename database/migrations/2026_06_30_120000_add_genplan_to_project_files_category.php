<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE project_files MODIFY category ENUM('hujjat','ruxsatnoma','chizma','boshqa','genplan') NOT NULL DEFAULT 'hujjat'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE project_files MODIFY category ENUM('hujjat','ruxsatnoma','chizma','boshqa') NOT NULL DEFAULT 'hujjat'");
    }
};
