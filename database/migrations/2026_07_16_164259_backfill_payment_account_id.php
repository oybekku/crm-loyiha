<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * account_id maydoni qo'shilishidan oldin yaratilgan to'lovlarni ularning
     * "method" (naqd/karta/bank) maydoniga qarab, o'sha turdagi yagona
     * hisobga avtomatik biriktiradi. Agar bir turda bir nechta hisob bo'lsa,
     * qaysi biriga tegishli ekanini aniqlab bo'lmagani uchun o'sha turdagi
     * to'lovlar o'tkazib yuboriladi (admin qo'lda biriktiradi).
     */
    public function up(): void
    {
        $accountsByType = DB::table('financial_accounts')
            ->select('id', 'type')
            ->get()
            ->groupBy('type');

        foreach ($accountsByType as $type => $accounts) {
            if ($accounts->count() !== 1) {
                continue;
            }

            DB::table('payments')
                ->where('method', $type)
                ->whereNull('account_id')
                ->update(['account_id' => $accounts->first()->id]);
        }
    }

    public function down(): void
    {
        // Qaytarilmaydi — qaysi to'lovlar ushbu migratsiya orqali biriktirilgani
        // aniq ma'lum emas, shu sababli xavfsiz down mavjud emas.
    }
};
