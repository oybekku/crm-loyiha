<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Oklad tarixi — "shu oydan boshlab" (UserCommissionRate kabi). Oy okladi =
        // effective_month <= oy bo'lgan eng oxirgi yozuv. users.base_salary — joriy oklad (ko'rsatish uchun).
        Schema::create('user_base_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('effective_month', 7); // Y-m
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'effective_month']);
        });

        // Avval belgilangan oklad hamma oylarga qo'shilardi — xuddi shunday qolsin
        foreach (DB::table('users')->where('base_salary', '>', 0)->get(['id', 'base_salary']) as $u) {
            DB::table('user_base_salaries')->insert([
                'user_id' => $u->id, 'amount' => $u->base_salary, 'effective_month' => '2000-01',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_base_salaries');
    }
};
