<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE projects MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'yangi'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('yangi','tolov_jarayonida','tekshirish','tolangan','tugallangan','taqdim_etilgan','bekor_qilingan') NOT NULL DEFAULT 'yangi'");
    }
};
