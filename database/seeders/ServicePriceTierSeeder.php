<?php

namespace Database\Seeders;

use App\Models\ServicePriceTier;
use Illuminate\Database\Seeder;

class ServicePriceTierSeeder extends Seeder
{
    public function run(): void
    {
        ServicePriceTier::truncate();

        $data = [
            // ──────────────────────────────────────────────────────────────
            // TOPOSYOMKA — sub: Toposyomka (maydon bo'yicha)
            // ──────────────────────────────────────────────────────────────
            ['service_key' => 'toposyomka', 'sub_service' => 'toposyomka',  'sub_service_label' => 'Toposyomka',                         'label' => '1 kv.m dan 200 kv.m gacha',                                            'price' => 515000,   'sort_order' => 1],
            ['service_key' => 'toposyomka', 'sub_service' => 'toposyomka',  'sub_service_label' => 'Toposyomka',                         'label' => '200 kv.m dan 600 kv.m gacha',                                          'price' => 824000,   'sort_order' => 2],
            ['service_key' => 'toposyomka', 'sub_service' => 'toposyomka',  'sub_service_label' => 'Toposyomka',                         'label' => '600 kv.m dan 1000 kv.m gacha',                                         'price' => 1030000,  'sort_order' => 3],
            ['service_key' => 'toposyomka', 'sub_service' => 'toposyomka',  'sub_service_label' => 'Toposyomka',                         'label' => '1000 kv.m dan 1500 kv.m gacha',                                        'price' => 1236000,  'sort_order' => 4],
            ['service_key' => 'toposyomka', 'sub_service' => 'toposyomka',  'sub_service_label' => 'Toposyomka',                         'label' => '1500 kv.m dan 2000 kv.m gacha',                                        'price' => 1442000,  'sort_order' => 5],
            ['service_key' => 'toposyomka', 'sub_service' => 'toposyomka',  'sub_service_label' => 'Toposyomka',                         'label' => '2000 kv.m dan 5000 kv.m gacha',                                        'price' => 2060000,  'sort_order' => 6],
            ['service_key' => 'toposyomka', 'sub_service' => 'toposyomka',  'sub_service_label' => 'Toposyomka',                         'label' => '0,5 gektardan 1,0 gektargacha',                                        'price' => 2884000,  'sort_order' => 7],
            ['service_key' => 'toposyomka', 'sub_service' => 'toposyomka',  'sub_service_label' => 'Toposyomka',                         'label' => '1,0 gektardan 2,0 gektargacha',                                        'price' => 4120000,  'sort_order' => 8],
            ['service_key' => 'toposyomka', 'sub_service' => 'toposyomka',  'sub_service_label' => 'Toposyomka',                         'label' => '3,0 gektardan yuqorisi uchun (har bir gektariga)',                     'price' => 1442000,  'sort_order' => 9],

            // TOPOSYOMKA — sub: Qoziq qo'qish
            ['service_key' => 'toposyomka', 'sub_service' => 'qoziq',       'sub_service_label' => "Qoziq qo'qish",                     'label' => '1 donadan 4 donagacha',                                                'price' => 515000,   'sort_order' => 1],
            ['service_key' => 'toposyomka', 'sub_service' => 'qoziq',       'sub_service_label' => "Qoziq qo'qish",                     'label' => '5 donadan 10 donagacha (har bir donasi uchun)',                        'price' => 123600,   'sort_order' => 2],
            ['service_key' => 'toposyomka', 'sub_service' => 'qoziq',       'sub_service_label' => "Qoziq qo'qish",                     'label' => '11 donadan yuqori (har bir donasi uchun)',                             'price' => 103000,   'sort_order' => 3],
            ['service_key' => 'toposyomka', 'sub_service' => 'qoziq',       'sub_service_label' => "Qoziq qo'qish",                     'label' => '1 dona 10 metrli nuqta uchun (BHM 10)',                               'price' => 4120000,  'sort_order' => 4],
            ['service_key' => 'toposyomka', 'sub_service' => 'qoziq',       'sub_service_label' => "Qoziq qo'qish",                     'label' => '1 dona 15 metrli nuqta uchun (BHM 15)',                               'price' => 6180000,  'sort_order' => 5],

            // TOPOSYOMKA — sub: QR kod
            ['service_key' => 'toposyomka', 'sub_service' => 'qr_kod',      'sub_service_label' => 'QR kod',                            'label' => 'Maydoni 0,5 ga va undan kichik (1-obyekt BHM 0,5)',                   'price' => 206000,   'sort_order' => 1],
            ['service_key' => 'toposyomka', 'sub_service' => 'qr_kod',      'sub_service_label' => 'QR kod',                            'label' => 'Maydoni 0,5 ga dan 1,0 gachan (1-obyekt BHM 0,8)',                   'price' => 329600,   'sort_order' => 2],
            ['service_key' => 'toposyomka', 'sub_service' => 'qr_kod',      'sub_service_label' => 'QR kod',                            'label' => 'Maydoni 1,0 ga dan 2,0 gachan (1-obyekt BHM 1,0)',                   'price' => 412000,   'sort_order' => 3],
            ['service_key' => 'toposyomka', 'sub_service' => 'qr_kod',      'sub_service_label' => 'QR kod',                            'label' => 'Maydoni 2,0 ga dan 5,0 gachan (1-obyekt BHM 1,3)',                   'price' => 535600,   'sort_order' => 4],
            ['service_key' => 'toposyomka', 'sub_service' => 'qr_kod',      'sub_service_label' => 'QR kod',                            'label' => "Maydoni 5,0 ga dan ortiq (qo'shimcha har bir gektari BHM 0,2)",      'price' => 824000,   'sort_order' => 5],

            // TOPOSYOMKA — sub: Akt (joyiga ko'chirish dalolatnomasi)
            ['service_key' => 'toposyomka', 'sub_service' => 'akt',         'sub_service_label' => "Akt (joyiga ko'chirish dalolatnomasi)", 'label' => '1 ta bino akt (4-6 tagacha qoziq)',                              'price' => 1030000,  'sort_order' => 1],
            ['service_key' => 'toposyomka', 'sub_service' => 'akt',         'sub_service_label' => "Akt (joyiga ko'chirish dalolatnomasi)", 'label' => '2 ta bino akt (10-12 tagacha qoziq)',                            'price' => 1442000,  'sort_order' => 2],
            ['service_key' => 'toposyomka', 'sub_service' => 'akt',         'sub_service_label' => "Akt (joyiga ko'chirish dalolatnomasi)", 'label' => '3 ta bino akt (12-15 tagacha qoziq)',                            'price' => 2060000,  'sort_order' => 3],
            ['service_key' => 'toposyomka', 'sub_service' => 'akt',         'sub_service_label' => "Akt (joyiga ko'chirish dalolatnomasi)", 'label' => '3 tadan ko\'p bino aktning har bir donasi uchun',                'price' => 618000,   'sort_order' => 4],
        ];

        ServicePriceTier::insert($data);
    }
}
