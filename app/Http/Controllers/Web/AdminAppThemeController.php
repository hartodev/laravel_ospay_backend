<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Support\AppThemeColors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Superadmin: atur warna aplikasi mobile (Konten > Warna Aplikasi).
 * Hasilnya langsung tersedia di GET /api/app/theme.
 */
class AdminAppThemeController extends Controller
{
    public function edit(): View
    {
        return view('admin.content.theme', [
            'theme' => AppThemeColors::current(),
            'defaults' => AppThemeColors::defaults(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'primary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ], [
            'primary.required' => 'Warna utama wajib diisi.',
            'primary.regex' => 'Format warna utama harus #RRGGBB, contoh #2563EB.',
            'secondary.required' => 'Warna kedua wajib diisi.',
            'secondary.regex' => 'Format warna kedua harus #RRGGBB, contoh #0D9488.',
        ]);

        $version = AppThemeColors::current()['version'] + 1;

        AppSetting::setValue(
            AppThemeColors::KEY,
            AppThemeColors::make($data['primary'], $data['secondary'], $version)
        );

        $redirect = redirect()
            ->route('admin.content.theme')
            ->with('success', 'Warna aplikasi disimpan. Aplikasi memakainya saat dibuka lagi atau kembali dari background.');

        // Teks putih di tombol/header jadi sulit dibaca kalau warna utama terlalu terang.
        if (AppThemeColors::contrastWithWhite($data['primary']) < 3.0) {
            $redirect->with('warning', 'Warna utama cukup terang, teks putih di tombol dan header bisa sulit dibaca. Pertimbangkan warna yang lebih gelap.');
        }

        return $redirect;
    }

    public function reset(): RedirectResponse
    {
        $theme = AppThemeColors::defaults();
        $theme['version'] = AppThemeColors::current()['version'] + 1;

        AppSetting::setValue(AppThemeColors::KEY, $theme);

        return redirect()
            ->route('admin.content.theme')
            ->with('success', 'Warna aplikasi dikembalikan ke warna awal.');
    }
}
