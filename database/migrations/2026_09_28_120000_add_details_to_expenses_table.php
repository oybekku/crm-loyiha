<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // "Yangi bux" → Kirim-chiqim → "Chiqim qo'shish" oynasidagi
            // qo'shimcha maydonlar. Hammasi ixtiyoriy (nullable) — eski
            // xarajatlar o'zgarmaydi, eski Buxgalteriya ularga tegmaydi.
            $table->unsignedBigInteger('project_id')->nullable()->after('salary_payment_id');
            $table->unsignedBigInteger('responsible_id')->nullable()->after('project_id');
            $table->text('note')->nullable()->after('comment');
            $table->string('attachment')->nullable()->after('note');
            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['project_id']);
            $table->dropColumn(['project_id', 'responsible_id', 'note', 'attachment']);
        });
    }
};
