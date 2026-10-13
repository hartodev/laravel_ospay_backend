<?php

use App\Http\Controllers\Web\AdminAgentController;
use App\Http\Controllers\Web\AdminBrandingController;
use App\Http\Controllers\Web\AdminCommissionController;
use App\Http\Controllers\Web\AdminContentController;
use App\Http\Controllers\Web\AdminProductController;
use App\Http\Controllers\Web\AgentController;
use App\Http\Controllers\Web\Auth\PortalController;
use App\Http\Controllers\Web\Auth\StoreRegisterController;
use App\Http\Controllers\Web\Auth\TenantAuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DepositController;
use App\Http\Controllers\Web\GuestCheckoutController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\TransactionController;
use App\Http\Controllers\Web\WalletController;
use App\Http\Middleware\WebCheckRole;
use App\Support\Tenancy;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AppThemeController as WebAppThemeController;


/*
|--------------------------------------------------------------------------
| Web Routes (Blade + session)
|--------------------------------------------------------------------------
|
| Login memakai session (guard "web"). Group terautentikasi memakai
| middleware auth:sanctum: Sanctum otomatis menerima session web, jadi
| token API (mobile) dan session web memakai user table yang sama.
|
| Callback Midtrans & IAK ada di routes/api.php (publik, tanpa CSRF).
|
*/

/*
|--------------------------------------------------------------------------
| MULTI-TOKO (TENANCY_MODE = path | subdomain)
|--------------------------------------------------------------------------
|
| Domain utama  (TENANCY_MAIN_DOMAIN)  : landing, beli tanpa login, portal
|                                         login, daftar toko, login superadmin.
| Halaman toko  : mode path      -> domain-utama/{toko}/login, /register
|                 mode subdomain -> {toko}.domain-utama/login, /register
| Dashboard dst : tanpa kode toko di URL. Hak akses ditentukan oleh akun
|                 (ResolveTenant + EnsureHostMatchesUser), bukan oleh URL.
|
| Route halaman toko mode path didaftarkan PALING BAWAH file ini, supaya
| route tetap seperti /login, /dashboard, /admin/... selalu menang.
|
*/

$mainDomain = config('tenancy.main_domain');

// Alamat lama panel konten; panel kini ada di dalam dashboard superadmin.
Route::redirect('/admin/content', '/admin/konten');

/*
|--------------------------------------------------------------------------
| DOMAIN UTAMA
|--------------------------------------------------------------------------
*/
Route::domain($mainDomain)->group(function () {

    Route::get('/', fn () => view('landing'))->name('home');

    /*
    | PEMBELIAN TAMU (tanpa login)
    |
    | Pembeli umum membeli dari landing page, bayar QRIS, lalu memantau pesanan
    | memakai KODE TRANSAKSI (ref_id). Semua transaksi dimiliki akun sistem
    | "Tamu". Untuk pembelian rutin, halaman memandu pembeli mendaftar.
    */
    Route::get('/beli', [GuestCheckoutController::class, 'catalog'])->name('guest.catalog');
    Route::get('/beli/{product}', [GuestCheckoutController::class, 'product'])
        ->where('product', '[A-Za-z0-9._\-]+')->name('guest.product');
    Route::post('/beli/{product}', [GuestCheckoutController::class, 'store'])
        ->where('product', '[A-Za-z0-9._\-]+')->middleware('throttle:10,1')->name('guest.store');

    Route::get('/lacak', [GuestCheckoutController::class, 'track'])->name('guest.track');
    Route::post('/lacak', [GuestCheckoutController::class, 'lookup'])->middleware('throttle:20,1')->name('guest.lookup');
    Route::get('/lacak/{code}', [GuestCheckoutController::class, 'status'])
        ->where('code', '[A-Za-z0-9\-]+')->middleware('throttle:60,1')->name('guest.status');
    Route::post('/lacak/{code}/cek', [GuestCheckoutController::class, 'refresh'])
        ->where('code', '[A-Za-z0-9\-]+')->middleware('throttle:12,1')->name('guest.refresh');

    Route::middleware('guest')->group(function () {
        // Gerbang "cari toko": isi subdomain -> diarahkan ke login toko.
        // Login langsung (semua peran: superadmin, agen, pelanggan) tanpa subdomain.
        Route::get('/login', [PortalController::class, 'show'])->name('login');
        Route::post('/login', [PortalController::class, 'login'])
            ->middleware('throttle:login')->name('login.store');

        // Jalur opsional "cari toko": isi subdomain -> halaman login toko.
        Route::post('/login/toko', [PortalController::class, 'redirectToStore'])
            ->middleware('throttle:login')->name('login.go');

        // Daftar toko baru (= daftar agen, menunggu persetujuan superadmin).
        Route::get('/daftar', [StoreRegisterController::class, 'create'])->name('register-agen');
        Route::post('/daftar', [StoreRegisterController::class, 'store'])
            ->middleware('throttle:register')->name('register-agen.store');
        Route::redirect('/register-agen', '/daftar');

        // Daftar user umum (tanpa agen). Pelanggan sebuah toko mendaftar
        // lewat halaman toko agennya (tokoabc.ospay.test/register).
        Route::get('/register', [PortalController::class, 'showRegister'])->name('register');
        Route::post('/register', [PortalController::class, 'register'])
            ->middleware('throttle:register')->name('register.store');

        // Superadmin tidak punya halaman login sendiri lagi: masuk lewat /login.
        // Alamat lama tetap dialihkan supaya bookmark tidak rusak.
        Route::redirect('/superadmin/login', '/login')->name('superadmin.login');
    });
});

/*
|--------------------------------------------------------------------------
| SUBDOMAIN TOKO  ({toko}.domain-utama)  -- hanya TENANCY_MODE=subdomain
|--------------------------------------------------------------------------
*/
if (! Tenancy::isPathMode()) {
    Route::domain('{tenant}.' . $mainDomain)->middleware('tenant')->group(function () {

        // Serah-terima login dari domain utama (link bertanda tangan, sekali pakai).
        Route::get('/sso', [TenantAuthController::class, 'sso'])
            ->middleware('signed')->name('tenant.sso');

        Route::get('/', fn() => auth()->check()
            ? redirect()->route('dashboard')
            : redirect()->route('tenant.login'))->name('tenant.home');

        Route::middleware('guest')->group(function () {
            Route::get('/login', [TenantAuthController::class, 'showLogin'])->name('tenant.login');
            Route::post('/login', [TenantAuthController::class, 'login'])
                ->middleware('throttle:login')->name('tenant.login.store');

            Route::get('/register', [TenantAuthController::class, 'showRegister'])->name('tenant.register');
            Route::post('/register', [TenantAuthController::class, 'register'])
                ->middleware('throttle:register')->name('tenant.register.store');
        });
    });
}

/*
|--------------------------------------------------------------------------
| AUTHENTICATED (semua role aktif)
|--------------------------------------------------------------------------
*/
Route::middleware(['tenant', 'auth:sanctum', 'tenant.member', WebCheckRole::class.':superadmin,agen,user'])->group(function () {

    Route::post('/logout', [TenantAuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');

    /*
    |----------------------------------------------------------------------
    | USER + AGEN: katalog produk & transaksi
    |----------------------------------------------------------------------
    */
    Route::middleware(WebCheckRole::class.':agen,user')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');

        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::post('/transactions/{transaction}/refresh', [TransactionController::class, 'refresh'])->name('transactions.refresh');
    });

    /*
    |----------------------------------------------------------------------
    | AGEN ONLY  (/agen/...)
    |----------------------------------------------------------------------
    */
    Route::middleware(WebCheckRole::class.':agen')->prefix('agen')->name('agen.')->group(function () {
        Route::get('/wallet', [WalletController::class, 'show'])->name('wallet');

        Route::get('/deposits', [DepositController::class, 'index'])->name('deposits.index');
        Route::get('/deposits/create', [DepositController::class, 'create'])->name('deposits.create');
        Route::post('/deposits', [DepositController::class, 'store'])->name('deposits.store');
        Route::get('/deposits/{deposit}', [DepositController::class, 'show'])->name('deposits.show');

        Route::get('/sub-users', [AgentController::class, 'subUsers'])->name('sub-users.index');
        Route::get('/sub-users/create', [AgentController::class, 'createSubUser'])->name('sub-users.create');
        Route::post('/sub-users', [AgentController::class, 'storeSubUser'])->name('sub-users.store');

        Route::get('/commissions', [AgentController::class, 'commissions'])->name('commissions');
    });

    /*
    |----------------------------------------------------------------------
    | SUPERADMIN ONLY  (/admin/...)
    |----------------------------------------------------------------------
    */
    Route::middleware(WebCheckRole::class.':superadmin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/agents', [AdminAgentController::class, 'index'])->name('agents.index');
        Route::post('/agents/{user}/approve', [AdminAgentController::class, 'approve'])->name('agents.approve');
        Route::post('/agents/{user}/reject', [AdminAgentController::class, 'reject'])->name('agents.reject');
        Route::post('/agents/{user}/suspend', [AdminAgentController::class, 'suspend'])->name('agents.suspend');
        Route::post('/agents/{user}/activate', [AdminAgentController::class, 'activate'])->name('agents.activate');

        Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
        Route::patch('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
        Route::post('/products/sync', [ProductController::class, 'sync'])->name('products.sync');
        Route::post('/products/import', [AdminProductController::class, 'import'])->name('products.import');

        Route::get('/commissions', [AdminCommissionController::class, 'index'])->name('commissions.index');
        Route::post('/commissions', [AdminCommissionController::class, 'store'])->name('commissions.store');
        Route::post('/commissions/backfill', [AdminCommissionController::class, 'backfill'])->name('commissions.backfill');
        Route::post('/commissions/payout-all', [AdminCommissionController::class, 'payoutAll'])->name('commissions.payout-all');
        Route::post('/commissions/{commission}/payout', [AdminCommissionController::class, 'payout'])->name('commissions.payout');
        Route::patch('/commissions/{rule}', [AdminCommissionController::class, 'update'])->name('commissions.update');
        Route::delete('/commissions/{rule}', [AdminCommissionController::class, 'destroy'])->name('commissions.destroy');

        // Tema warna (mobile + web)
        Route::get('/tema', [WebAppThemeController::class, 'edit'])->name('theme.edit');
        Route::post('/tema', [WebAppThemeController::class, 'update'])->name('theme.update');

        // Konten halaman Akun di aplikasi mobile
        Route::prefix('konten')->name('content.')->group(function () {
            Route::get('/', [AdminContentController::class, 'index'])->name('index');
            Route::get('/faq', [AdminContentController::class, 'faqs'])->name('faqs');
            Route::post('/faq', [AdminContentController::class, 'storeFaq'])->name('faqs.store');
            Route::put('/faq/{faq}', [AdminContentController::class, 'updateFaq'])->name('faqs.update');
            Route::delete('/faq/{faq}', [   AdminContentController::class, 'destroyFaq'])->name('faqs.destroy');
            Route::get('/halaman/{slug}', [AdminContentController::class, 'page'])->name('page');
            Route::put('/halaman/{slug}', [AdminContentController::class, 'updatePage'])->name('page.update');
            Route::get('/tentang', [AdminContentController::class, 'appInfo'])->name('app-info');
            Route::put('/tentang', [AdminContentController::class, 'updateAppInfo'])->name('app-info.update');

            // Logo aplikasi mobile
            Route::get('/logo', [AdminBrandingController::class, 'edit'])->name('logo');
            Route::post('/logo', [AdminBrandingController::class, 'update'])->name('logo.update');
            Route::delete('/logo', [AdminBrandingController::class, 'reset'])->name('logo.reset');
            Route::get('/logo/ikon-android', [AdminBrandingController::class, 'downloadIcons'])->name('logo.icons'); // <- tambahkan ini
        });
    });
});

/*
|--------------------------------------------------------------------------
| HALAMAN TOKO MODE PATH  (domain-utama/{toko}/...)  -- WAJIB PALING BAWAH
|--------------------------------------------------------------------------
|
| Nama route sama dengan versi subdomain (tenant.login, dst.), jadi view &
| controller tidak perlu tahu mode yang dipakai. Alur:
|   daftar toko (pending) -> superadmin approve -> /{toko} aktif.
| Toko yang belum ada / pending / nonaktif ditangani middleware "tenant"
| (halaman "toko belum bisa dibuka").
|
*/
if (Tenancy::isPathMode()) {
    Route::domain($mainDomain)
        ->prefix('{tenant}')
        ->where(['tenant' => Tenancy::routePattern()])
        ->middleware('tenant')
        ->group(function () {

            Route::get('/', fn() => auth()->check()
                ? redirect()->route('dashboard')
                : redirect()->route('tenant.login'))->name('tenant.home');

            Route::middleware('guest')->group(function () {
                Route::get('/login', [TenantAuthController::class, 'showLogin'])->name('tenant.login');
                Route::post('/login', [TenantAuthController::class, 'login'])
                    ->middleware('throttle:login')->name('tenant.login.store');

                Route::get('/register', [TenantAuthController::class, 'showRegister'])->name('tenant.register');
                Route::post('/register', [TenantAuthController::class, 'register'])
                    ->middleware('throttle:register')->name('tenant.register.store');
            });
        });
}
