<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\AppBranding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
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

    public function reset(): RedirectResponse
    {
        AppBranding::reset();

        return redirect()
            ->route('admin.content.logo')
            ->with('success', 'Logo dikembalikan ke logo bawaan aplikasi.');
    }
}
