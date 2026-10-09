<?php

return [

    'commission' => [
        // Role yang boleh membuka halaman Komisi di aplikasi.
        // Besar komisi diatur lewat tabel commission_rules, bukan di sini.
        'view_roles' => ['agen', 'superadmin'],
    ],
];
