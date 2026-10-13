<?php

/*
|--------------------------------------------------------------------------
| Multi-toko (path atau subdomain)
|--------------------------------------------------------------------------
|
| 1 kode toko (slug) = 1 toko = 1 akun agen. Pelanggan (role "user") yang
| daftar lewat halaman toko otomatis menjadi sub-user agen tersebut
| (users.parent_agent_id). Kode toko disimpan di kolom users.subdomain
| (nama kolom dipertahankan supaya tidak perlu migrasi).
|
| MODE "path" (bawaan, tidak butuh DNS/SSL wildcard):
|   ospay.test              -> landing, portal login, daftar toko, superadmin
|   ospay.test/tokoabc      -> halaman toko "tokoabc" (login / daftar pelanggan)
|
| MODE "subdomain" (butuh DNS wildcard + SSL wildcard):
|   tokoabc.ospay.test      -> halaman toko "tokoabc"
|
*/

return [

    // "path" (ospay.test/tokoabc) atau "subdomain" (tokoabc.ospay.test).
    'mode' => env('TENANCY_MODE', 'path'),

    // Domain utama TANPA port. Port otomatis ikut request (mis. :8002).
    'main_domain' => env('TENANCY_MAIN_DOMAIN', 'ospay.test'),

    // Panjang subdomain yang boleh didaftarkan.
    'min_length' => 3,
    'max_length' => 30,

    // Subdomain yang tidak boleh dipakai toko.
    'reserved' => [
        'www', 'mail', 'ftp', 'smtp', 'pop', 'imap', 'webmail',
        'admin', 'administrator', 'superadmin', 'root',
        'api', 'app', 'apps', 'portal', 'dashboard',
        'login', 'logout', 'register', 'daftar', 'auth',
        'static', 'cdn', 'assets', 'storage', 'files', 'media',
        'test', 'staging', 'dev', 'demo', 'beta', 'localhost',
        'support', 'help', 'docs', 'blog', 'status', 'ns1', 'ns2',
        'ospay', 'beli', 'lacak',

        // Alamat milik aplikasi ini sendiri (mode path: satu segmen URL
        // pertama = kode toko, jadi tidak boleh bentrok dengan route lain).
        'profile', 'products', 'transactions', 'wallet', 'deposits',
        'konten', 'tema', 'agen', 'agents', 'sso', 'up', 'tenant', 'toko',
        'store', 'akun', 'callback', 'midtrans', 'iak', 'webhook',
        'build', 'images', 'css', 'js', 'fonts', 'vendor', 'robots',
        'sanctum', 'broadcasting', 'index',
    ],
];
