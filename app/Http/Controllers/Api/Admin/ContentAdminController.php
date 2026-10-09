<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\ContentPage;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mengelola konten halaman Akun. Hanya untuk role superadmin.
 * Perubahan langsung terlihat di aplikasi, tanpa rilis ulang.
 */
class ContentAdminController extends Controller
{
    // GET /api/admin/faqs  (semua FAQ, termasuk yang nonaktif)
    public function indexFaqs(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        return response()->json(['data' => Faq::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()]);
    }

    // GET /api/admin/pages
    public function indexPages(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        return response()->json(['data' => ContentPage::query()
            ->orderBy('slug')
            ->get(['id', 'slug', 'title', 'is_active', 'updated_at'])]);
    }

    // GET /api/admin/pages/{slug}
    public function showPage(Request $request, string $slug): JsonResponse
    {
        $this->authorizeAdmin($request);

        return response()->json(['data' => ContentPage::query()
            ->where('slug', $slug)
            ->firstOrFail()]);
    }

    // DELETE /api/admin/pages/{slug}
    public function destroyPage(Request $request, string $slug): JsonResponse
    {
        $this->authorizeAdmin($request);
        ContentPage::query()->where('slug', $slug)->firstOrFail()->delete();

        return response()->json(['message' => 'Halaman dihapus.']);
    }

    // GET /api/admin/app-info
    public function showAppInfo(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        return response()->json(['data' => AppSetting::getValue('app.info')]);
    }

    // POST /api/admin/faqs
    public function storeFaq(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);
        $faq = Faq::query()->create($this->validateFaq($request));

        return response()->json(['data' => $faq], 201);
    }

    // PUT /api/admin/faqs/{faq}
    public function updateFaq(Request $request, Faq $faq): JsonResponse
    {
        $this->authorizeAdmin($request);
        $faq->update($this->validateFaq($request));

        return response()->json(['data' => $faq]);
    }

    // DELETE /api/admin/faqs/{faq}
    public function destroyFaq(Request $request, Faq $faq): JsonResponse
    {
        $this->authorizeAdmin($request);
        $faq->delete();

        return response()->json(['message' => 'FAQ dihapus.']);
    }

    // PUT /api/admin/pages/{slug}   (dibuat kalau belum ada)
    public function upsertPage(Request $request, string $slug): JsonResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'title' => 'required|string|max:150',
            'intro' => 'nullable|string',
            'sections' => 'required|array|min:1',
            'sections.*.title' => 'required|string|max:150',
            'sections.*.paragraphs' => 'required|array|min:1',
            'sections.*.paragraphs.*' => 'required|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $page = ContentPage::query()->updateOrCreate(['slug' => $slug], $data);

        return response()->json(['data' => $page]);
    }

    // PUT /api/admin/app-info
    public function updateAppInfo(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'description' => 'required|string',
            'latest_version' => 'nullable|string|max:20',
            'support.whatsapp' => 'nullable|string|max:40',
            'support.email' => 'nullable|email|max:120',
            'support.hours' => 'nullable|string|max:120',
            'highlights' => 'nullable|array',
            'highlights.*.icon' => 'nullable|string|max:20',
            'highlights.*.title' => 'required|string|max:100',
            'highlights.*.description' => 'required|string|max:300',
        ]);

        AppSetting::setValue('app.info', $data);

        return response()->json(['data' => $data]);
    }

    private function validateFaq(Request $request): array
    {
        return $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'sort_order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'superadmin', 403, 'Hanya untuk superadmin.');
    }
}
