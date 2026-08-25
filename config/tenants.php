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
    ],

];
