<?php
// Tambahkan ke routes/web.php, di dalam group superadmin yang sudah ada
// (sesuaikan middleware 'role:superadmin' dengan milik project kamu)

use App\Http\Controllers\Web\Admin\ThemeController;

Route::middleware(['auth', 'role:superadmin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/theme',        [ThemeController::class, 'edit'])->name('theme.edit');
        Route::put('/theme',        [ThemeController::class, 'update'])->name('theme.update');
        Route::post('/theme/reset', [ThemeController::class, 'reset'])->name('theme.reset');
    });
