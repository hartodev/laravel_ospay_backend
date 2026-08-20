<?php

return [
    'username' => env('IAK_USERNAME'),

    // Ganti ke true kalau sudah pakai akun production IAK
    'is_production' => env('IAK_PRODUCTION', false),

    // api_key beda antara sandbox (dev) dan production, IAK generate otomatis per environment
    'api_key' => [
        'dev'  => env('IAK_API_KEY_DEV'),
        'prod' => env('IAK_API_KEY_PROD'),
    ],

    // Base URL prepaid (pulsa, PLN, game, dll) - pakai versi v2 (bukan legacy)
    'prepaid_url' => [
        'dev'  => env('IAK_PREPAID_URL_DEV', 'https://prepaid.iak.dev'),
        'prod' => env('IAK_PREPAID_URL_PROD', 'https://prepaid.iak.id'),
    ],

    // Base URL postpaid (tagihan PDAM, listrik pascabayar, dll)
    'postpaid_url' => [
        'dev'  => env('IAK_POSTPAID_URL_DEV', 'https://testpostpaid.mobilepulsa.net'),
        'prod' => env('IAK_POSTPAID_URL_PROD', 'https://mobilepulsa.net'),
    ],
];