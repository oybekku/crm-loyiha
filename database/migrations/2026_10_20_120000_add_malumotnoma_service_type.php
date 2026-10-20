<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OLD = [
        'toposyomka', 'geologiya', 'eskiz_loyiha', 'texnik_korik',
        'ariza', 'konstruksiya', 'arxitektura', 'smeta',
        'ichki_dizayn', 'tashqi_dizayn', 'mualliflik_nazorati',
        'laboratoriya', 'kadastr', 'maxsus_xizmat',
    ];

    // Yangi xizmat turi "Ma'lumotnoma" — ro'yxat OXIRIGA qo'shiladi (mavjud qatorlar o'zgarmaydi)
    public function up(): void
    {
        $this->setEnum([...self::OLD, 'malumotnoma']);
    }

    public function down(): void
    {
        DB::table('project_services')->where('service_name', 'malumotnoma')->exists()
            ? throw new RuntimeException("project_services'da 'malumotnoma' qatorlari bor — avval ularni o'chiring")
            : $this->setEnum(self::OLD);
    }

    private function setEnum(array $values): void
    {
        $list = implode(',', array_map(fn ($v) => "'{$v}'", $values));
        DB::statement("ALTER TABLE project_services MODIFY service_name ENUM({$list}) NOT NULL");
    }
};
