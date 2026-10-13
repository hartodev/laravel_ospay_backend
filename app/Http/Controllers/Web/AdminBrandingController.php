<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\AppBranding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;
use Illuminate\View\View;

/**
 * Superadmin: ganti logo aplikasi mobile (Konten > Logo Aplikasi).
 * Hasilnya langsung tersedia di GET /api/app/branding.
 */
class AdminBrandingController extends Controller
{
    public function edit(): View
    {
        $branding = AppBranding::current();

        return view('admin.content.logo', [
            'hasLogo' => $branding['logo_path'] !== null,
            'version' => $branding['version'],
            'iconSupported' => AppBranding::iconPackSupported(),
            'iconSizes' => AppBranding::ICON_SIZES,
            'customIcons' => AppBranding::customIcons(),
            'iconVersion' => AppBranding::iconVersion(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if ($request->filled('icon_density')) {
            return $this->uploadIcon($request);
        }

        $data = $request->validate([
            'logo' => [
                'required',
                'file',
                'mimes:png,jpg,jpeg,webp',
                'max:2048',
                'dimensions:min_width=128,min_height=128',
            ],
        ], [
            'logo.required' => 'Pilih file logo terlebih dahulu.',
            'logo.mimes' => 'Format logo harus PNG, JPG, atau WEBP.',
            'logo.max' => 'Ukuran logo maksimal 2 MB.',
            'logo.dimensions' => 'Ukuran logo minimal 128 x 128 piksel.',
        ]);

        AppBranding::store($data['logo']);

        return redirect()
            ->route('admin.content.logo')
            ->with('success', 'Logo aplikasi disimpan. Aplikasi memakainya saat dibuka lagi atau kembali dari background.');
    }

    public function reset(Request $request): RedirectResponse
    {
        if ($request->filled('icon_density')) {
            $d = $request->input('icon_density');
            AppBranding::resetIcon($d === 'all' ? null : (AppBranding::isDensity($d) ? $d : null));

            return redirect()->route('admin.content.logo')
                ->with('success', $d === 'all' ? 'Semua ikon kustom dihapus. Ikon dibuat otomatis dari logo.' : 'Ikon kustom dihapus. Ikon ini dibuat otomatis dari logo.');
        }

        AppBranding::reset();

        return redirect()
            ->route('admin.content.logo')
            ->with('success', 'Logo dikembalikan ke logo bawaan aplikasi.');
    }

    private function uploadIcon(Request $request): RedirectResponse
    {
        $d = (string) $request->input('icon_density');
        abort_unless(AppBranding::isDensity($d), 404);
        $px = AppBranding::ICON_SIZES[$d];

        $request->validate([
            'icon' => ['required', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048', "dimensions:min_width={$px},min_height={$px}"],
        ], [
            'icon.required' => 'Pilih file ikon terlebih dahulu.',
            'icon.mimes' => 'Format ikon harus PNG, JPG, atau WEBP.',
            'icon.max' => 'Ukuran ikon maksimal 2 MB.',
            'icon.dimensions' => "Ukuran ikon minimal {$px} x {$px} piksel.",
        ]);

        try {
            AppBranding::storeIcon($d, $request->file('icon'));
        } catch (Throwable $e) {
            return redirect()->route('admin.content.logo')->with('error', $e->getMessage());
        }

        return redirect()->route('admin.content.logo')
            ->with('success', 'Ikon ' . str_replace('mipmap-', '', $d) . " ({$px} px) disimpan.");
    }

    /** GET ?d=mipmap-hdpi -> gambar ikon (pratinjau); tanpa parameter -> unduh ZIP paket ikon. */
    public function downloadIcons(Request $request): BinaryFileResponse|RedirectResponse|\Illuminate\Http\Response
    {
        if ($d = $request->query('d')) {
            $png = AppBranding::iconPng((string) $d);
            abort_if($png === null, 404);

            return response($png, 200, ['Content-Type' => 'image/png', 'Cache-Control' => 'private, max-age=0, must-revalidate']);
        }

        try {
            $file = AppBranding::buildIconPack();
        } catch (Throwable $e) {
            return redirect()->route('admin.content.logo')->with('error', $e->getMessage());
        }

        return response()
            ->download($file, 'ospay-android-icons.zip', ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }
}
