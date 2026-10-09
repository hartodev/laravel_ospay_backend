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
 * Satu form email + password untuk SEMUA peran, tanpa perlu tahu subdomain:
 *   - superadmin   -> sesi dibuat di domain utama -> dashboard
 *   - user umum    -> (role user TANPA agen) sesi di domain utama -> dashboard
 *   - agen         -> diarahkan ke subdomain tokonya -> dashboard toko
 *   - pelanggan toko -> (role user di bawah agen) diarahkan ke subdomain
 *                      toko agennya -> dashboard
 *
 * Karena cookie sesi bersifat per-host, perpindahan ke subdomain memakai
 * link serah-terima bertanda tangan (sekali pakai, 60 detik) yang diproses
 * TenantAuthController::sso().
 *
 * Tidak ada tombol / halaman khusus superadmin: superadmin masuk lewat form
 * yang sama, jadi tidak ada pintu terpisah yang bisa ditebak orang lain.
 *
 * "Cari toko" (isi subdomain) tetap ada sebagai jalur opsional.
 */
class PortalController extends Controller
{
    public function show(Request $request)
    {
        // ?subdomain=xxx dipakai setelah daftar toko supaya kolom langsung terisi.
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

        // Agen / pelanggan toko: cari tokonya, lalu serahkan login ke subdomain toko.
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

        // Token sekali pakai: link hanya berlaku 60 detik dan hanya sekali dibuka.
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

    /** Jalur opsional: isi subdomain toko -> diarahkan ke halaman login tokonya. */
    public function redirectToStore(Request $request)
    {
        $request->validate([
            'subdomain' => ['required', 'string', 'max:120'],
        ], [
            'subdomain.required' => 'Isi subdomain toko Anda.',
        ]);

        $sub = $this->normalize($request->input('subdomain'));
        $store = $sub !== '' ? Tenancy::findStore($sub) : null;

        if (! $store) {
            return back()->withInput()
                ->withErrors(['subdomain' => 'Toko dengan subdomain itu tidak ditemukan.']);
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

    /** Terima "tokoabc", "tokoabc.ospay.test", atau "http://tokoabc.ospay.test:8002". */
    private function normalize(string $input): string
    {
        $value = strtolower(trim($input));
        $value = preg_replace('#^https?://#', '', $value);
        $value = explode('/', $value)[0];
        $value = preg_replace('/:\d+$/', '', $value);

        $suffix = '.' . Tenancy::mainDomain();
        if (str_ends_with($value, $suffix)) {
            $value = substr($value, 0, -strlen($suffix));
        }

        return $value;
    }
}
