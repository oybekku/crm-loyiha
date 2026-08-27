<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE projects MODIFY COLUMN work_status ENUM('yangi', 'jarayonda', 'rad_qilindi', 'tayyor', 'tolov_jarayonda', 'kelishildi', 'kelishilmadi') NOT NULL DEFAULT 'yangi'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE projects MODIFY COLUMN work_status ENUM('yangi', 'jarayonda', 'rad_qilindi', 'tayyor', 'tolov_jarayonda') NOT NULL DEFAULT 'yangi'");
    }
};
