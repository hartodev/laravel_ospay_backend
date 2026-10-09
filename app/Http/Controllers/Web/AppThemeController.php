<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AppTheme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppThemeController extends Controller
{
    public function edit(): View
    {
        return view('admin.theme', ['theme' => AppTheme::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (AppTheme::COLOR_FIELDS as $field) {
            $rules[$field] = ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'];
        }

        $data = $request->validate($rules, [
            'regex' => 'Format warna harus #RRGGBB, contoh #2563EB.',
        ]);

        $theme = AppTheme::current();
        $theme->fill(array_map('strtoupper', $data));
        // Naikkan versi supaya aplikasi mobile mau mengganti warnanya.
        $theme->version = $theme->version + 1;
        $theme->save();

        return redirect()
            ->route('admin.theme.edit')
            ->with('status', 'Tema disimpan (versi ' . $theme->version . '). Aplikasi akan memakai warna ini saat dibuka berikutnya.');
    }
}
