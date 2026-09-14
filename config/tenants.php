<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shahar (tenant) bazalari
    |--------------------------------------------------------------------------
    |
    | Har bir yozuv — poddomen nomi => shu shahar uchun MySQL ulanish
    | ma'lumotlari. Yangi shahar qo'shilganda faqat shu ro'yxatga yangi
    | qator qo'shiladi, boshqa kod o'zgarmaydi.
    |
    */

    'andijon.makonn.uz' => [
        'database' => env('DB_ANDIJON_DATABASE', 'elkayo0i_andijon'),
        'username' => env('DB_ANDIJON_USERNAME', 'elkayo0i_andijon'),
        'password' => env('DB_ANDIJON_PASSWORD'),
        'label'    => 'Andijon',
        // Ommaviy bosh sahifada (landing) shu shahar uchun ko'rsatiladigan aloqa ma'lumotlari.
        'phone'    => '+998951481991',
        'address'  => "Andijon viloyati, Baliqchi tumani, Baliqchi shox ko'chasi",
        // Obloshka (chizma jildi) varag'idagi shahar/tuman yozuvlari
        'obloshka_shahar' => 'Andijon',
        'obloshka_tuman'  => 'Andijon viloyati, Baliqchi tumani',
    ],

];
