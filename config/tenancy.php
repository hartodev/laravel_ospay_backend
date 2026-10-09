<?php

/*
|--------------------------------------------------------------------------
| Multi-toko berbasis subdomain
|--------------------------------------------------------------------------
|
| 1 subdomain = 1 toko = 1 akun agen. Pelanggan (role "user") yang daftar
| lewat subdomain toko otomatis menjadi sub-user agen tersebut
| (users.parent_agent_id).
|
| Contoh dengan main_domain "ospay.test":
|   ospay.test              -> portal "cari toko", daftar toko, login superadmin
|   tokoabc.ospay.test      -> login / daftar pelanggan / dashboard toko "tokoabc"
|
*/

return [

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
    ],
];
