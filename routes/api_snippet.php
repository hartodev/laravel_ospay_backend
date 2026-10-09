<?php
// Tambahkan ke routes/api.php (publik, JANGAN di dalam auth:sanctum)

use App\Http\Controllers\Api\AppThemeController;

Route::get('/app/theme', [AppThemeController::class, 'show']);
