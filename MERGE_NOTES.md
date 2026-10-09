# Penggabungan laravel_ospay_web -> laravel_ospay_backend

Prinsip: bagian API TIDAK diubah. Web ditambahkan sebagai file baru.

## 1. TIDAK diubah sama sekali (identik byte-per-byte dengan backend asli)
routes/api.php, routes/api_content.php, seluruh app/Http/Controllers/Api/* (termasuk
IakCallbackController & CallbackController), config/*, database/migrations/*,
CheckRole (API), Http/Kernel, Models: Transactions, WalletTransaction, CommissionRule,
Faq, ContentPage, AppSetting, Commission, Observer, Wallet*Service, Iak/*
(kecuali ProductSyncService), seeder UserSeeder / DatabaseSeeder / AppContentSeeder.

## 2. File BARU untuk web (tidak menimpa apa pun)
- app/Http/Controllers/Web/* (11 controller)
- resources/views/** (kecuali welcome) + menu "Konten Aplikasi" di layouts/app.blade.php
- app/Http/Middleware/WebCheckRole.php  (CheckRole versi web: redirect ke login;
  CheckRole API tetap versi asli yang selalu JSON)
- app/Console/Commands/ImportPriceList.php, app/Services/PricelistImportService.php
- routes/web.php (route web + /admin/content)
- database/seeders/ContentTestSeeder.php, ContentRestoreSeeder.php

## 3. Callback IAK (diminta digabung)
Controller & route callback IAK identik di kedua project, jadi tidak ada yang diubah di sana.
Yang berbeda ada di logika yang dipanggilnya, dan itu sekarang memakai versi web:
- TransactionService (finalize -> applyIakResponse dengan lockForUpdate, status PENDING tidak
  menimpa status final, commit/release saldo & refund tidak bisa dobel)
- CommissionService (komisi untuk transaksi sukses)

## 4. File bersama yang DIGANTI versi web (dibutuhkan halaman web; API ikut memakainya)
Services: DepositService, Midtrans/CreateTransactionVaService, Midtrans/CreateVaService,
Iak/ProductSyncService.
Models: User, Products, Deposits, Commissions, Wallets  (backend memakai method casts()
gaya Laravel 11 yang diabaikan di Laravel 10; versi web memakai $casts, dan web membutuhkan
cast array/datetime pada Deposits).
Lainnya: RouteServiceProvider (HOME=/dashboard), Console/Kernel (+ImportPriceList),
ExpireStaleDeposits (cek status Midtrans sebelum meng-expire deposit).
Metode lama yang dipakai API (purchase, finalize, refreshStatus, create, markSuccess, dst.)
tetap ada dengan signature sama.

## 5. composer
composer.json backend + satu baris: "phpoffice/phpspreadsheet": "^5.10" (untuk impor price list).
composer.lock TIDAK diubah, jadi jalankan:
    composer require phpoffice/phpspreadsheet:^5.10

## Setelah ekstrak (.env dan vendor/ tidak ikut)
    composer require phpoffice/phpspreadsheet:^5.10
    php artisan optimize:clear
    php artisan migrate     # tidak ada tabel baru

## Catatan
- Migration 2026_10_05_000002_create_commissions_table (bawaan backend, tidak saya ubah) membuat
  tabel `commissions` yang sudah dibuat migration 2026_08_20_040531, jadi `php artisan migrate`
  pada database KOSONG akan gagal di migration itu. Di database yang sudah berjalan tidak masalah.
- ContentController asli dipertahankan (tanpa header no-store dari perbaikan sebelumnya).
- .env.example project web berisi DB_PASSWORD asli; file itu tidak dipakai di sini.

## Panel Konten Aplikasi (web superadmin)
Menu sidebar "Konten Aplikasi" -> /admin/konten (alamat lama /admin/content diarahkan ke sini).
Login memakai session web biasa; tidak perlu token. Membaca/menulis tabel faqs, content_pages,
app_settings yang sama dengan endpoint publik app mobile; routes/api*.php dan Api/* tidak diubah.
- app/Http/Controllers/Web/AdminContentController.php
- resources/views/admin/content/{index,faqs,page,app-info,_tabs}.blade.php
- routes/web.php (grup admin -> prefix konten)
File lama resources/views/admin/content.blade.php (halaman token) tidak lagi dipakai.

## Pembelian tamu (tanpa login) dari landing page
Alur: landing -> /beli (pilih produk) -> /beli/{kode produk} (isi nomor) -> bayar QRIS ->
/lacak/{kode transaksi} (status, SN, pengembalian dana). Ajakan daftar tampil di setiap halaman.
- Tanpa perubahan tabel/migration. Transaksi tamu dimiliki satu akun sistem "Tamu OSPay"
  (guest@example.com, role user, status suspended -> tidak bisa dipakai login; dibuat otomatis).
- KODE TRANSAKSI = ref_id (TRX- + 20 karakter acak). /lacak hanya membuka transaksi milik akun tamu;
  transaksi pengguna/agen terdaftar tidak bisa dibuka lewat kode.
- Produk yang dijual ke tamu: aktif, price_user > base_price (sudah diberi margin), bukan pasca bayar.
- Memakai TransactionService::purchase -> QRIS Midtrans -> callback yang sama -> order IAK. Callback tidak diubah.
- Rate limit: beli 10/menit, cari kode 20/menit, status 60/menit, cek status 12/menit (per IP).
- Pengguna yang sudah login dialihkan ke katalog akunnya (harga & riwayat mengikuti akun).
File baru: app/Services/GuestCheckoutService.php, app/Http/Controllers/Web/GuestCheckoutController.php,
resources/views/guest/*, resources/views/layouts/public.blade.php. Diubah: routes/web.php, landing.blade.php.
