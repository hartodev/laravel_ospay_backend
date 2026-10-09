<?php

use App\Http\Controllers\Api\AdminAgentController;
use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CallbackController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\DepositController;
use App\Http\Controllers\Api\IakCallbackController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AdminProductController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AppThemeController;
use App\Http\Controllers\Api\BrandingController;

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
|
| Endpoint yang tidak membutuhkan authentication Sanctum.
|
*/

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

/*
| Semua endpoint di grup ini membaca toko (tenant) dari subdomain
| atau header X-Tenant lewat middleware "tenant".
|
|   - toko-abc.ospay.test  -> tenant "toko-abc"
|   - ospay.test           -> situs utama (tanpa tenant)
*/

Route::middleware('tenant')->group(function () {

    // Cek kode toko + ambil nama toko (dipakai aplikasi Flutter)
    Route::get('/tenant', [
        AuthController::class,
        'tenant',
    ]);

    Route::post('/register', [
        AuthController::class,
        'register',
    ])->middleware('throttle:register');

    Route::post('/register-agen', [
        AuthController::class,
        'registerAgen',
    ])->middleware('throttle:register');

    Route::post('/login', [
        AuthController::class,
        'login',
    ])->middleware('throttle:login');
});

/*
|--------------------------------------------------------------------------
| PAYMENT CALLBACK
|--------------------------------------------------------------------------
|
| Endpoint ini PUBLIC karena dipanggil oleh server eksternal:
|
| - Midtrans
| - IAK
|
| Jangan pasang auth:sanctum di sini.
|
*/

/*
|--------------------------------------------------------------------------
| MIDTRANS CALLBACK
|--------------------------------------------------------------------------
*/

Route::post('/callback/midtrans', [
    CallbackController::class,
    'midtrans',
]);

/*
|--------------------------------------------------------------------------
| IAK CALLBACK
|--------------------------------------------------------------------------
*/

Route::post(
    '/callback/iak',
    IakCallbackController::class
);

Route::get('/app/theme', [AppThemeController::class, 'show']);
Route::get('/app/branding', [BrandingController::class, 'show']);
Route::get('/app/logo', [BrandingController::class, 'logo']);

/*
|--------------------------------------------------------------------------
| AUTHENTICATED
|--------------------------------------------------------------------------
*/

Route::middleware(['tenant', 'auth:sanctum', 'tenant.access'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTH USER
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [
        AuthController::class,
        'logout',
    ]);

    Route::get('/me', [
        AuthController::class,
        'me',
    ]);

    // Token FCM perangkat (notifikasi transaksi sukses)
    Route::post('/device-token', [DeviceTokenController::class, 'store']);
    Route::delete('/device-token', [DeviceTokenController::class, 'destroy']);

    Route::put('/profile', [
        AuthController::class,
        'updateProfile',
    ]);

    Route::put('/profile/password', [
        AuthController::class,
        'changePassword',
    ]);


    /*
    |--------------------------------------------------------------------------
    | USER + AGEN
    |--------------------------------------------------------------------------
    |
    | User dan agen sama-sama dapat:
    |
    | - melihat produk
    | - melakukan transaksi
    | - melihat transaksi sendiri
    |
    */

    Route::middleware('role:agen,user')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | PRODUCTS
        |--------------------------------------------------------------------------
        */

        Route::get('/products', [
            ProductController::class,
            'index',
        ]);


        /*
        |--------------------------------------------------------------------------
        | TRANSACTIONS
        |--------------------------------------------------------------------------
        */

        Route::get('/transactions', [
            TransactionController::class,
            'index',
        ]);

        Route::post('/transactions', [
            TransactionController::class,
            'store',
        ]);

        Route::get('/transactions/{transaction}', [
            TransactionController::class,
            'show',
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | AGEN ONLY
    |--------------------------------------------------------------------------
    |
    | Semua fitur khusus agen berada di bawah:
    |
    | /api/agen/...
    |
    */

    Route::middleware('role:agen')
        ->prefix('agen')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | WALLET
            |--------------------------------------------------------------------------
            */

            Route::get('/wallet', [
                WalletController::class,
                'show',
            ]);


            /*
            |--------------------------------------------------------------------------
            | DEPOSIT
            |--------------------------------------------------------------------------
            |
            | Flow:
            |
            | POST /agen/deposits
            |        ↓
            | Midtrans
            |        ↓
            | QRIS
            |        ↓
            | Midtrans callback
            |        ↓
            | Deposit paid
            |        ↓
            | Wallet credit
            |
            */

            Route::get('/deposits', [
                DepositController::class,
                'index',
            ]);

            Route::post('/deposits', [
                DepositController::class,
                'store',
            ]);

            Route::get('/deposits/{deposit}', [
                DepositController::class,
                'show',
            ]);


            /*
            |--------------------------------------------------------------------------
            | SUB USERS
            |--------------------------------------------------------------------------
            */

            Route::get('/sub-users', [
                AgentController::class,
                'subUsers',
            ]);

            Route::post('/sub-users', [
                AgentController::class,
                'storeSubUser',
            ]);


            /*
            |--------------------------------------------------------------------------
            | COMMISSIONS
            |--------------------------------------------------------------------------
            */

            Route::get('/commissions', [
                AgentController::class,
                'commissions',
            ]);
        });


    /*
    |--------------------------------------------------------------------------
    | SUPERADMIN ONLY
    |--------------------------------------------------------------------------
    |
    | Semua fitur admin berada di bawah:
    |
    | /api/admin/...
    |
    */

    Route::middleware('role:superadmin')
        ->prefix('admin')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | AGENT MANAGEMENT
            |--------------------------------------------------------------------------
            */

            Route::get('/agents', [
                AdminAgentController::class,
                'index',
            ]);

            Route::get('/agents/pending', [
                AdminAgentController::class,
                'pending',
            ]);

            Route::post('/agents/{user}/approve', [
                AdminAgentController::class,
                'approve',
            ]);

            Route::post('/agents/{user}/reject', [
                AdminAgentController::class,
                'reject',
            ]);

            Route::post('/agents/{user}/suspend', [
                AdminAgentController::class,
                'suspend',
            ]);

            Route::post('/agents/{user}/activate', [
                AdminAgentController::class,
                'activate',
            ]);


            /*
            |--------------------------------------------------------------------------
            | PRODUCT SYNC
            |--------------------------------------------------------------------------
            |
            | Untuk sementara hanya sync produk dari IAK.
            |
            | Management harga lengkap dapat dibuat
            | pada tahap berikutnya.
            |
            */
            Route::post('/products/sync', [
            ProductController::class,
            'sync',
        ]);

        Route::get('/products', [
            AdminProductController::class,
            'index',
        ]);

            Route::patch('/products/{product}', [
                AdminProductController::class,
                'update',
            ]);
        });
});

/*
| Rute halaman Akun (app-info, faqs, pages, komisi, kelola konten).
*/
require __DIR__ . '/api_content.php';
