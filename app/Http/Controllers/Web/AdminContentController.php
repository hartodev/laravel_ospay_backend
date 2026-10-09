<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\ContentPage;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Superadmin: kelola isi halaman Akun di aplikasi mobile
 * (Pusat Bantuan, Kebijakan Privasi, Syarat & Ketentuan, Tentang Aplikasi).
 *
 * Membaca/menulis tabel faqs, content_pages, dan app_settings yang sama dengan
 * endpoint publik /api/faqs, /api/pages/{slug}, /api/app-info, jadi perubahan
 * langsung terlihat di aplikasi tanpa rilis ulang. Endpoint API tidak diubah.
 */
class AdminContentController extends Controller
{
    /** Halaman dokumen yang bisa diedit: slug => nama tampilan. */
    private const PAGES = [
        'privacy-policy' => 'Kebijakan Privasi',
        'terms' => 'Syarat & Ketentuan',
    ];

    /** Ikon yang dikenali aplikasi untuk poin unggulan "Tentang Aplikasi". */
    private const ICONS = [
        'bolt' => 'Petir (cepat)',
        'apps' => 'Kotak-kotak (banyak layanan)',
        'receipt' => 'Struk (riwayat)',
        'wallet' => 'Dompet (saldo)',
    ];

    private const HIGHLIGHT_ROWS = 6;

    // ------------------------------------------------------------ ringkasan
    public function index(): View
    {
        return view('admin.content.index', [
            'faqTotal' => Faq::query()->count(),
            'faqActive' => Faq::query()->where('is_active', true)->count(),
            'pages' => ContentPage::query()->whereIn('slug', array_keys(self::PAGES))->get()->keyBy('slug'),
            'pageNames' => self::PAGES,
            'info' => AppSetting::getValue('app.info'),
        ]);
    }

    // ------------------------------------------------------------------ FAQ
    public function faqs(): View
    {
        return view('admin.content.faqs', [
            'faqs' => Faq::query()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function storeFaq(Request $request): RedirectResponse
    {
        $data = $this->validateFaq($request);
        $data['sort_order'] ??= ((int) Faq::query()->max('sort_order')) + 1;

        Faq::query()->create($data);

        return redirect()->route('admin.content.faqs')->with('success', 'FAQ ditambahkan.');
    }

    public function updateFaq(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validateFaq($request));

        return redirect()->route('admin.content.faqs')->with('success', 'FAQ disimpan.');
    }

    public function destroyFaq(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('admin.content.faqs')->with('success', 'FAQ dihapus.');
    }

    // ------------------------------------------- Kebijakan Privasi & Syarat
    public function page(string $slug): View
    {
        abort_unless(isset(self::PAGES[$slug]), 404);

        $page = ContentPage::query()->where('slug', $slug)->first();

        return view('admin.content.page', [
            'slug' => $slug,
            'pageName' => self::PAGES[$slug],
            'page' => $page,
            'sectionsText' => $this->sectionsToText($page?->sections ?? []),
        ]);
    }

    public function updatePage(Request $request, string $slug): RedirectResponse
    {
        abort_unless(isset(self::PAGES[$slug]), 404);

        $data = $request->validate([
            'title' => 'required|string|max:150',
            'intro' => 'nullable|string',
            'sections_text' => 'required|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $sections = $this->textToSections($data['sections_text']);

        ContentPage::query()->updateOrCreate(['slug' => $slug], [
            'title' => $data['title'],
            'intro' => $data['intro'] ?? null,
            'sections' => $sections,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.content.page', $slug)->with('success', self::PAGES[$slug].' disimpan.');
    }

    // ------------------------------------------------------ Tentang Aplikasi
    public function appInfo(): View
    {
        $info = AppSetting::getValue('app.info', []);

        $rows = array_values($info['highlights'] ?? []);
        while (count($rows) < self::HIGHLIGHT_ROWS) {
            $rows[] = ['icon' => 'bolt', 'title' => '', 'description' => ''];
        }

        return view('admin.content.app-info', [
            'info' => $info,
            'rows' => array_slice($rows, 0, self::HIGHLIGHT_ROWS),
            'icons' => self::ICONS,
        ]);
    }

    public function updateAppInfo(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'description' => 'required|string',
            'latest_version' => ['nullable', 'string', 'max:20', 'regex:/^\d+(\.\d+){0,2}$/'],
            'whatsapp' => 'nullable|string|max:40',
            'email' => 'nullable|email|max:120',
            'hours' => 'nullable|string|max:120',
            'highlights' => 'nullable|array|max:'.self::HIGHLIGHT_ROWS,
            'highlights.*.icon' => 'nullable|in:'.implode(',', array_keys(self::ICONS)),
            'highlights.*.title' => 'nullable|string|max:100',
            'highlights.*.description' => 'nullable|string|max:300',
        ], [
            'latest_version.regex' => 'Format versi harus angka bertitik, contoh 1.2.0.',
        ]);

        // Baris yang judul dan deskripsinya kosong diabaikan; baris setengah terisi ditolak.
        $highlights = [];
        foreach ($data['highlights'] ?? [] as $i => $row) {
            $title = trim((string) ($row['title'] ?? ''));
            $desc = trim((string) ($row['description'] ?? ''));

            if ($title === '' && $desc === '') {
                continue;
            }
            if ($title === '' || $desc === '') {
                throw ValidationException::withMessages([
                    "highlights.$i.title" => 'Judul dan deskripsi poin unggulan harus diisi keduanya.',
                ]);
            }

            $highlights[] = [
                'icon' => $row['icon'] ?? 'bolt',
                'title' => $title,
                'description' => $desc,
            ];
        }

        AppSetting::setValue('app.info', [
            'description' => $data['description'],
            'latest_version' => $data['latest_version'] ?: null,
            'support' => [
                'whatsapp' => $data['whatsapp'] ?? null,
                'email' => $data['email'] ?? null,
                'hours' => $data['hours'] ?? null,
            ],
            'highlights' => $highlights,
        ]);

        return redirect()->route('admin.content.app-info')->with('success', 'Info aplikasi dan kontak bantuan disimpan.');
    }

    // ---------------------------------------------------------------- helper
    private function validateFaq(Request $request): array
    {
        $data = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        if (! isset($data['sort_order'])) {
            unset($data['sort_order']);
        }

        return $data;
    }

    /**
     * Format teks yang diedit admin:
     *
     *   ## Judul bagian
     *
     *   Paragraf pertama.
     *
     *   Paragraf kedua.
     *
     *   ## Judul bagian berikutnya
     *   ...
     */
    private function textToSections(string $text): array
    {
        $lines = preg_split('/\R/', trim($text));
        $sections = [];
        $current = null;
        $buffer = [];

        $flush = function () use (&$current, &$buffer, &$sections) {
            if ($current === null) {
                return;
            }
            $paragraphs = [];
            $para = [];
            foreach ($buffer as $line) {
                if (trim($line) === '') {
                    if ($para) {
                        $paragraphs[] = implode("\n", $para);
                        $para = [];
                    }
                } else {
                    $para[] = rtrim($line);
                }
            }
            if ($para) {
                $paragraphs[] = implode("\n", $para);
            }
            $sections[] = ['title' => $current, 'paragraphs' => $paragraphs];
            $buffer = [];
        };

        foreach ($lines as $line) {
            if (preg_match('/^##\s+(.+)$/', $line, $m)) {
                $flush();
                $current = trim($m[1]);

                continue;
            }
            if ($current === null) {
                if (trim($line) !== '') {
                    throw ValidationException::withMessages([
                        'sections_text' => 'Mulai isi dengan judul bagian pada baris yang diawali "## ", contoh: ## Informasi yang Kami Kumpulkan.',
                    ]);
                }

                continue;
            }
            $buffer[] = $line;
        }
        $flush();

        if (! $sections) {
            throw ValidationException::withMessages(['sections_text' => 'Minimal satu bagian (## Judul) diperlukan.']);
        }
        foreach ($sections as $s) {
            if (! $s['paragraphs']) {
                throw ValidationException::withMessages([
                    'sections_text' => 'Bagian "'.$s['title'].'" belum punya paragraf.',
                ]);
            }
        }

        return $sections;
    }

    private function sectionsToText(array $sections): string
    {
        $out = [];
        foreach ($sections as $s) {
            $out[] = '## '.($s['title'] ?? '')."\n\n".implode("\n\n", $s['paragraphs'] ?? []);
        }

        return implode("\n\n", $out);
    }
}
