<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\ContentPage;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;

/**
 * Konten publik halaman Akun. Tidak perlu login.
 */
class ContentController extends Controller
{
    // GET /api/app-info
    public function appInfo(): JsonResponse
    {
        $info = AppSetting::getValue('app.info');
        abort_if($info === null, 404, 'Info aplikasi belum diatur.');

        return response()->json(['data' => $info]);
    }

    // GET /api/faqs
    public function faqs(): JsonResponse
    {
        $faqs = Faq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'question', 'answer']);

        return response()->json(['data' => $faqs]);
    }

    // GET /api/pages/{slug}   (privacy-policy | terms | ...)
    public function page(string $slug): JsonResponse
    {
        $page = ContentPage::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json(['data' => [
            'slug' => $page->slug,
            'title' => $page->title,
            'intro' => $page->intro,
            'updated_at' => $page->updated_at?->toIso8601String(),
            'sections' => $page->sections,
        ]]);
    }
}
