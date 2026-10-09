<?php

use App\Http\Controllers\Api\Admin\ContentAdminController;
use App\Http\Controllers\Api\CommissionController;
use App\Http\Controllers\Api\ContentController;
use Illuminate\Support\Facades\Route;

/*
| Rute halaman Akun. Di-load dari routes/api.php, jadi otomatis
| memakai prefix /api.
*/

// Publik
Route::get('/app-info', [ContentController::class, 'appInfo']);
Route::get('/faqs', [ContentController::class, 'faqs']);
Route::get('/pages/{slug}', [ContentController::class, 'page']);

Route::middleware('auth:sanctum')->group(function () {
    // Komisi (agen)
    Route::get('/commissions/summary', [CommissionController::class, 'summary']);
    Route::get('/commissions', [CommissionController::class, 'index']);

    // Kelola konten (superadmin): CRUD FAQ, halaman dokumen, info aplikasi
    Route::middleware('role:superadmin')->prefix('admin')->group(function () {
        Route::get('/faqs', [ContentAdminController::class, 'indexFaqs']);
        Route::post('/faqs', [ContentAdminController::class, 'storeFaq']);
        Route::put('/faqs/{faq}', [ContentAdminController::class, 'updateFaq']);
        Route::delete('/faqs/{faq}', [ContentAdminController::class, 'destroyFaq']);

        Route::get('/pages', [ContentAdminController::class, 'indexPages']);
        Route::get('/pages/{slug}', [ContentAdminController::class, 'showPage']);
        Route::put('/pages/{slug}', [ContentAdminController::class, 'upsertPage']);
        Route::delete('/pages/{slug}', [ContentAdminController::class, 'destroyPage']);

        Route::get('/app-info', [ContentAdminController::class, 'showAppInfo']);
        Route::put('/app-info', [ContentAdminController::class, 'updateAppInfo']);
    });
});
