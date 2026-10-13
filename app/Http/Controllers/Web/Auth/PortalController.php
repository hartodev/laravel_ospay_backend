<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Tenancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Halaman login di domain utama (ospay.test/login).
 *
 * Satu form email + password untuk SEMUA peran, tanpa perlu tahu kode toko:
 *   - superadmin     -> dashboard
 *   - user umum      -> (role user TANPA agen) dashboard
 *   - agen           -> dashboard toko (toko harus sudah disetujui)
 *   - pelanggan toko -> (role user di bawah agen) dashboard toko agennya
 *
 * Mode "path"      : semua toko satu host, jadi sesi langsung dibuat di sini.
 * Mode "subdomain" : cookie sesi bersifat per-host, jadi perpindahan ke
 *                    subdomain memakai link serah-terima bertanda tangan
 *                    (sekali pakai, 60 detik) yang diproses
 *                    TenantAuthController::sso().
 *
 * Tidak ada tombol / halaman khusus superadmin: superadmin masuk lewat form
 * yang sama, jadi tidak ada pintu terpisah yang bisa ditebak orang lain.
 *
 * "Cari toko" (isi kode toko) tetap ada sebagai jalur opsional.
 */
class PortalController extends Controller
{
    public function show(Request $request)
    {
        // ?subdomain=xxx (kode toko) dipakai setelah daftar toko supaya kolom langsung terisi.
        return view('auth.portal', [
            'subdomain' => $request->query('subdomain'),
        ]);
    }

    /** Login langsung dari domain utama (semua peran). */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        // Satu pesan untuk semua kegagalan supaya keberadaan akun tidak bocor.
        if (! $user
            || ! Hash::check($data['password'], $user->password)
            || ! in_array($user->role, ['superadmin', 'agen', 'user'], true)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if ($user->isPending()) {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda masih menunggu persetujuan superadmin.'],
            ]);
        }

        if (! $user->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda sedang dinonaktifkan. Silakan hubungi toko atau superadmin.'],
            ]);
        }

        $remember = $request->boolean('remember');

        // Superadmin & user umum (tidak terdaftar di agen mana pun): sesi di domain utama.
        if ($user->isSuperAdmin() || ($user->isUser() && ! $user->parent_agent_id)) {
            Auth::guard('web')->login($user, $remember);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        // Agen / pelanggan toko: cari tokonya dan pastikan toko itu aktif.
        $store = $user->isAgent() ? $user : $user->parentAgent;

        if (! $store || blank($store->subdomain)) {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda belum terhubung ke toko. Silakan hubungi superadmin.'],
            ]);
        }

        if ($store->isPending()) {
            throw ValidationException::withMessages([
                'email' => ['Toko ini belum disetujui superadmin. Mohon tunggu persetujuan.'],
            ]);
        }

        if (! $store->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['Toko ini sedang tidak aktif. Hubungi superadmin untuk informasi lebih lanjut.'],
            ]);
        }

        // Mode path: satu host, satu sesi. Cukup login di sini.
        if (Tenancy::isPathMode()) {
            Auth::guard('web')->login($user, $remember);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        // Mode subdomain. Token sekali pakai: link hanya berlaku 60 detik dan hanya sekali dibuka.
        $nonce = Str::random(40);
        Cache::put('sso_login:' . $nonce, $user->id, now()->addSeconds(60));

        $url = URL::temporarySignedRoute(
            'tenant.sso',
            now()->addSeconds(60),
            [
                'tenant' => $store->subdomain,
                'nonce' => $nonce,
                'remember' => $remember ? 1 : 0,
            ]
        );

        return redirect()->away($url);
    }

    /** Halaman daftar user umum (tanpa agen). */
    public function showRegister()
    {
        return view('auth.user-register');
    }

    /**
     * Daftar user umum di domain utama: langsung aktif, TANPA agen
     * (parent_agent_id = null), membayar per transaksi lewat Midtrans.
     * Pelanggan sebuah toko tetap mendaftar lewat halaman toko agennya.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique' => 'Email ini sudah terdaftar.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'user',
            'status' => 'active',
        ]);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Registrasi berhasil. Selamat datang!');
    }

    /** Jalur opsional: isi kode toko -> diarahkan ke halaman login tokonya. */
    public function redirectToStore(Request $request)
    {
        $request->validate([
            'subdomain' => ['required', 'string', 'max:120'],
        ], [
            'subdomain.required' => 'Isi kode toko Anda.',
        ]);

        $sub = $this->normalize($request->input('subdomain'));
        $store = $sub !== '' ? Tenancy::findStore($sub) : null;

        if (! $store) {
            return back()->withInput()
                ->withErrors(['subdomain' => 'Toko dengan kode itu tidak ditemukan.']);
        }

        if ($store->isPending()) {
            return back()->withInput()
                ->withErrors(['subdomain' => 'Toko ini belum disetujui superadmin. Mohon tunggu persetujuan.']);
        }

        if (! $store->isActive()) {
            return back()->withInput()
                ->withErrors(['subdomain' => 'Toko ini sedang tidak aktif. Hubungi superadmin untuk informasi lebih lanjut.']);
        }

        return redirect()->away(Tenancy::url($store->subdomain, '/login', $request));
    }

    /**
     * Terima "tokoabc", "ospay.test/tokoabc", "http://ospay.test:8002/tokoabc/login"
     * (mode path) atau "tokoabc.ospay.test" (mode subdomain).
     */
    private function normalize(string $input): string
    {
        $value = strtolower(trim($input));
        $value = preg_replace('#^https?://#', '', $value);

        if (Tenancy::isPathMode()) {
            // Buang "ospay.test[:port]/" di depan, lalu ambil segmen pertama saja.
            $value = preg_replace('#^' . preg_quote(Tenancy::mainDomain(), '#') . '(:\d+)?/#', '', $value);

            return trim(explode('/', ltrim($value, '/'))[0]);
        }

        $value = explode('/', $value)[0];
        $value = preg_replace('/:\d+$/', '', $value);

        $suffix = '.' . Tenancy::mainDomain();
        if (str_ends_with($value, $suffix)) {
            $value = substr($value, 0, -strlen($suffix));
        }

        return $value;
    }
}
