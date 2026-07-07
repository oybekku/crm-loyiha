<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ketma-ket tartib raqami (1, 2, 3...) — hech qachon takrorlanmaydi/qaytmaydi
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedInteger('seq_no')->nullable()->after('number');
        });

        // O'chirilса ham raqam saqlanishi uchun alohida hisoblagich
        if (!Schema::hasTable('counters')) {
            Schema::create('counters', function (Blueprint $table) {
                $table->string('name')->primary();
                $table->unsignedBigInteger('value')->default(0);
            });
        }

        // Mavjud loyihalarни yaratilgan vaqti bo'yicha 1, 2, 3... qilib to'ldiramiz
        // (o'chirilganlar ham kiradi — ular ham o'z raqamini "band" qiladi)
        $i = 0;
        foreach (DB::table('projects')->orderBy('created_at')->orderBy('id')->pluck('id') as $id) {
            $i++;
            DB::table('projects')->where('id', $id)->update(['seq_no' => $i]);
        }

        // Hisoblagichni oxirgi raqamга o'rnatamiz — yangilar shundan davom etadi
        DB::table('counters')->updateOrInsert(['name' => 'project_seq'], ['value' => $i]);

        // Endi barchasi to'lgan — yagona (unique) indeks qo'shamiz
        Schema::table('projects', function (Blueprint $table) {
            $table->unique('seq_no');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['seq_no']);
            $table->dropColumn('seq_no');
        });
    }
};
