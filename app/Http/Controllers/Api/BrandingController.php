<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AppBranding;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Logo aplikasi untuk Flutter. Publik (dipanggil sebelum login).
 */
class BrandingController extends Controller
{
    /** GET /api/app/branding */
    public function show(): JsonResponse
    {
        return response()->json([
            'data' => [
                // null = pakai logo bawaan yang ada di dalam aplikasi.
                'logo_url' => AppBranding::logoUrl(),
                'version' => AppBranding::current()['version'],
            ],
        ]);
    }

    /** GET /api/app/logo */
    public function logo(): BinaryFileResponse
    {
        $path = AppBranding::logoPath();

        abort_if($path === null, 404, 'Logo belum diatur.');

        return response()->file(
            Storage::disk(AppBranding::DISK)->path($path),
            ['Cache-Control' => 'public, max-age=86400']
        );
    }
}
