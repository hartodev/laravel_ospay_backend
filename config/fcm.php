<?php

$credentials = env(
    'FIREBASE_CREDENTIALS',
    storage_path('app/firebase/service-account.json')
);

// Jalur relatif (mis. "storage/app/firebase/...") dihitung dari root proyek,
// bukan dari folder tempat PHP dijalankan.
if ($credentials && ! preg_match('#^([A-Za-z]:[\\\\/]|/|\\\\\\\\)#', $credentials)) {
    $credentials = base_path($credentials);
}

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging (HTTP v1)
    |--------------------------------------------------------------------------
    |
    | credentials : path file JSON service account dari Firebase Console
    |               (Project settings > Service accounts > Generate new key).
    |               JANGAN di-commit ke Git.
    | project_id  : kosong = diambil dari file JSON.
    | enabled     : false = notifikasi dimatikan (mis. di lokal tanpa Firebase).
    |
    */

    'enabled' => env('FCM_ENABLED', true),

    'credentials' => $credentials,

    'project_id' => env('FIREBASE_PROJECT_ID'),

    // Harus sama dengan channel yang dibuat di aplikasi Flutter.
    'android_channel_id' => env('FCM_ANDROID_CHANNEL', 'ospay_transaksi'),
];
