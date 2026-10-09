<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Services\ThemeService;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function edit()
    {
        return view('admin.theme.edit', [
            'current' => ThemeService::current(),
            'presets' => $this->presets(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'primary'   => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ], [
            'primary.regex'   => 'Format warna utama harus hex, contoh #1E5BFF.',
            'secondary.regex' => 'Format warna sekunder harus hex, contoh #0B2A8A.',
        ]);

        $this->save(strtoupper($data['primary']), strtoupper($data['secondary']));

        return back()->with('success', 'Warna aplikasi berhasil diperbarui.');
    }

    public function reset()
    {
        $this->save(ThemeService::DEFAULT_PRIMARY, ThemeService::DEFAULT_SECONDARY);

        return back()->with('success', 'Warna dikembalikan ke default.');
    }

    private function save(string $primary, string $secondary): void
    {
        AppSetting::put('theme.primary', $primary);
        AppSetting::put('theme.secondary', $secondary);
        AppSetting::put('theme.version', (int) AppSetting::get('theme.version', 1) + 1);
    }

    private function presets(): array
    {
        return [
            ['name' => 'Biru (Default)', 'primary' => '#1E5BFF', 'secondary' => '#0B2A8A'],
            ['name' => 'Hijau',          'primary' => '#16A34A', 'secondary' => '#14532D'],
            ['name' => 'Merah',          'primary' => '#DC2626', 'secondary' => '#7F1D1D'],
            ['name' => 'Oranye',         'primary' => '#EA580C', 'secondary' => '#7C2D12'],
            ['name' => 'Ungu',           'primary' => '#7C3AED', 'secondary' => '#4C1D95'],
            ['name' => 'Teal',           'primary' => '#0D9488', 'secondary' => '#134E4A'],
        ];
    }
}
