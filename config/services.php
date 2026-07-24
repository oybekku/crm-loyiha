<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Eskiz.uz SMS shlyuzi
    'eskiz' => [
        'email'         => env('ESKIZ_EMAIL'),
        'password'      => env('ESKIZ_PASSWORD'),
        'from'          => env('ESKIZ_FROM', '4546'),
        // "Loyiha tayyor" tugmasi bosilganda ketadigan matn.
        // MUHIM: aynan shu matn Eskiz kabinetida moderatsiyadan o'tishi kerak.
        'ready_message' => env('ESKIZ_READY_MESSAGE',
            "Eskiz loyihangiz tayyor. Bog‘lanish: +998 77 091-91-01 MY PERFECT HOME"),
    ],

    // Telegram bot — muhim amallarni (o'chirish, narx o'zgartirish) tasdiqlash kodi uchun
    'telegram' => [
        'bot_token'    => env('TELEGRAM_BOT_TOKEN'),
        'bot_username' => env('TELEGRAM_BOT_USERNAME'), // @ belgisisiz, masalan: BestHomeCrmBot
        'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
    ],

];
