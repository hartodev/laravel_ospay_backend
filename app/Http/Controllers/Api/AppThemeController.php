<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppTheme;
use Illuminate\Http\JsonResponse;

class AppThemeController extends Controller
{
    /** GET /api/app/theme - publik, dipanggil aplikasi sebelum login. */
    public function show(): JsonResponse
    {
        return response()->json([
            'data' => AppTheme::current()->toApi(),
        ]);
    }
}
