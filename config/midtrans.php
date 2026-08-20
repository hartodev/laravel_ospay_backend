<?php

return [
    'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
    'client_key'  => env('MIDTRANS_CLIENT_KEY'),
    'server_key'  => env('MIDTRANS_SERVER_KEY'),

    // Ganti ke true kalau sudah pindah ke akun production Midtrans
    'is_production' => env('MIDTRANS_PRODUCTION', false),
    'is_sanitized'  => true,
    'is_3ds'        => env('MIDTRANS_IS_3DS', false),
];