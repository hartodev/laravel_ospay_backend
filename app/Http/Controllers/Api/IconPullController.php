<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AppBranding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Ikon launcher Android untuk skrip tool/pull_icons.dart di proyek Flutter.
 * Dilindungi token rahasia (header X-Icon-Token), bukan login.
 */
class IconPullController extends Controller
{
    /** GET /api/app/icons */
    public function __invoke(Request $request): JsonResponse
    {
        $token = (string) config('ospay.icon_pull_token', '');

        // Fitur mati kalau token belum diisi / terlalu pendek.
        abort_if(strlen($token) < 16, 404);
        abort_unless(hash_equals($token, (string) $request->header('X-Icon-Token', '')), 403, 'Token salah.');

        $icons = [];
        foreach (array_keys(AppBranding::ICON_SIZES) as $folder) {
            $png = AppBranding::iconPng($folder);
            if ($png !== null) {
                $icons[$folder] = base64_encode($png);
            }
        }

        abort_if($icons === [], 404, 'Belum ada logo atau ikon di superadmin.');

        return response()->json([
            'data' => [
                'version' => AppBranding::current()['version'] . '.' . AppBranding::iconVersion(),
                'icons' => $icons,
            ],
        ], 200, ['Cache-Control' => 'no-store']);
    }
}
