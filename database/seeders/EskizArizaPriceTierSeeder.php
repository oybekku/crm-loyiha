<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServicePriceTier;

class EskizArizaPriceTierSeeder extends Seeder
{
    public function run(): void
    {
        // ── Eskiz loyiha — AutoCad ──────────────────────────────────────────
        $autocad = [
            ['1 kv.m dan 50 kv.m gachan (har bir kv.m uchun)',     37080],
            ['50 kv.m dan 75 kv.m gachan (har bir kv.m uchun)',     24720],
            ['75 kv.m dan 100 kv.m gachan (har bir kv.m uchun)',    20600],
            ['100 kv.m dan 150 kv.m gachan (har bir kv.m uchun)',   16480],
            ['150 kv.m dan 200 kv.m gachan (har bir kv.m uchun)',   14420],
            ['200 kv.m dan 250 kv.m gachan (har bir kv.m uchun)',   13390],
            ['250 kv.m dan 300 kv.m gachan (har bir kv.m uchun)',   12360],
            ['300 kv.m dan 350 kv.m gachan (har bir kv.m uchun)',   11330],
            ['350 kv.m dan 400 kv.m gachan (har bir kv.m uchun)',   10300],
            ['400 kv.m dan 450 kv.m gachan (har bir kv.m uchun)',    9270],
            ['450 kv.m dan 500 kv.m gachan (har bir kv.m uchun)',    8240],
            ['500 kv.m dan yuqorisi (har bir kv.m uchun)',           7210],
        ];

        foreach ($autocad as $i => [$label, $price]) {
            ServicePriceTier::create([
                'service_key'       => 'eskiz_loyiha',
                'sub_service'       => 'autocad',
                'sub_service_label' => '"AutoCad" Loyiha',
                'label'             => $label,
                'price'             => $price,
                'sort_order'        => $i + 1,
            ]);
        }

        // ── Ariza ──────────────────────────────────────────────────────────
        $ariza = [
            ['Avtotrasnport vositalarini boshqarish uchun ishonchnoma berish (chet elga olib chiqib ketish huquqisiz) 25%', 103000],
            ['Elektr Gaz va Suv tarmoqlariga ulanish uchun ariza berish',                                                   41200],
            ["Elektron raqamli imzo olish (ERI) 15%",                                                                       61800],
            ["Mavzuli so'rovlarni ijro etish (Arxiv) 30%",                                                                 123600],
            ["Loyiha-smeta hujjatlarini kelishish (Qurulishga ruxsat) 100%",                                               412000],
        ];

        foreach ($ariza as $i => [$label, $price]) {
            ServicePriceTier::create([
                'service_key'       => 'ariza',
                'sub_service'       => 'ariza',
                'sub_service_label' => 'Ariza',
                'label'             => $label,
                'price'             => $price,
                'sort_order'        => $i + 1,
            ]);
        }
    }
}
