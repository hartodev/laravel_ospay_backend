<?php

return [
    /*
    | Folder res Android yang diisi langsung oleh superadmin (hanya APP_ENV=local),
    | dipisah koma. Contoh di .env:
    | OSPAY_ANDROID_RES_PATHS="E:/Colab_with_salma/project_ospay/ospay/android/app/src/main/res,E:/Colab_with_salma/project_ospay/ospay/android/app/src/dev/res"
    */
    'android_res_paths' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('OSPAY_ANDROID_RES_PATHS', ''))
    ))),

    /*
    | Token rahasia untuk skrip tool/pull_icons.dart di proyek Flutter (GET /api/app/icons).
    | Kosong atau kurang dari 16 karakter = fitur mati (404). Buat dengan: php -r "echo bin2hex(random_bytes(24));"
    */
    'icon_pull_token' => (string) env('OSPAY_ICON_PULL_TOKEN', ''),
];
