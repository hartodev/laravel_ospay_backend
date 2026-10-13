<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Tenancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Daftar toko baru (= daftar agen) di domain utama.
 *
 * Alur: daftar (status pending) -> superadmin approve -> alamat toko aktif
 * (mode path: domain-utama/{kode}). Selama pending, alamat tokonya belum bisa
 * dibuka (halaman "toko belum aktif"). Field & kolom tetap bernama "subdomain"
 * supaya kompatibel dengan API/mobile; artinya = kode toko.
 */
class StoreRegisterController extends Controller
{
    public function create()
    {
        return view('auth.store-register');
    }

    public function store(Request $request)
    {
        $request->merge([
            'subdomain' => strtolower(trim((string) $request->input('subdomain'))),
        ]);

        $min = (int) config('tenancy.min_length', 3);
        $max = (int) config('tenancy.max_length', 30);

        $data = $request->validate([
            'store_name' => ['required', 'string', 'max:80'],
            'subdomain' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($min, $max) {
                    if (strlen($value) < $min || strlen($value) > $max) {
                        return $fail("Kode toko harus {$min}-{$max} karakter.");
                    }
                    if (! preg_match(Tenancy::pattern(), $value)) {
                        return $fail('Kode toko hanya boleh huruf kecil, angka, dan tanda minus (tidak diawali/diakhiri minus).');
                    }
                    if (Tenancy::isReserved($value)) {
                        return $fail('Kode toko ini tidak boleh digunakan, silakan pilih yang lain.');
                    }
                },
                'unique:users,subdomain',
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'subdomain.unique' => 'Kode toko sudah dipakai toko lain.',
            'email.unique' => 'Email ini sudah terdaftar.',
        ]);

        $agent = User::create([
            'name' => $data['name'],
            'store_name' => $data['store_name'],
            'subdomain' => $data['subdomain'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => 'agen',
            'status' => 'pending',
        ]);

        return redirect()
            ->route('login', ['subdomain' => $agent->subdomain])
            ->with('success', "Pendaftaran toko berhasil. Alamat toko Anda: {$agent->storeHost()}. "
                . 'Toko aktif setelah disetujui superadmin.');
    }
}
