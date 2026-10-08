<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // "Qilinadigan ishlar ro'yxati" (Ariza → ✅ Ishlar): bajarilgan ishlar kalitlari
    // va loyihaga qo'lda qo'shilgan qo'shimcha ishlar — {"done":[...], "extra":[{id,label,done}]}
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->json('work_checklist')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('work_checklist');
        });
    }
};
