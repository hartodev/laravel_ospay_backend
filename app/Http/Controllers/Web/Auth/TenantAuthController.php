<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Tenancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Login & daftar pelanggan di halaman toko (ospay.test/tokoabc di mode path,
 * tokoabc.ospay.test di mode subdomain), plus logout untuk semua alamat.
 * Toko (agen pemilik kode toko) sudah divalidasi & disediakan oleh
 * middleware ResolveTenant lewat $request->attributes->get('tenant').
 */
class TenantAuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.tenant-login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $store = $request->attributes->get('tenant');
        $user = User::where('email', $data['email'])->first();

        // Satu pesan untuk semua kegagalan (akun tidak ada / password salah /
        // akun milik toko lain) supaya keberadaan akun tidak bocor.
        if (! $user
            || ! Hash::check($data['password'], $user->password)
            || ! Tenancy::belongsToStore($user, $store)) {
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

        Auth::guard('web')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Serah-terima login dari domain utama (ospay.test/login).
     *
     * Dilindungi middleware "signed" (link bertanda tangan, 60 detik) dan
     * nonce sekali pakai di cache: link yang dibuka ulang / dipalsukan ditolak.
     */
    public function sso(Request $request)
    {
        $store = $request->attributes->get('tenant');

        $userId = Cache::pull('sso_login:' . (string) $request->query('nonce'));
        $user = $userId ? User::find($userId) : null;

        if (! $user
            || ! Tenancy::belongsToStore($user, $store)
            || ! $user->isActive()) {
            return redirect()->route('tenant.login')->withErrors([
                'email' => 'Sesi login tidak valid atau sudah kedaluwarsa. Silakan masuk kembali.',
            ]);
        }

        Auth::guard('web')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function showRegister()
    {
        return view('auth.tenant-register');
    }

    /** Pelanggan daftar lewat subdomain toko -> otomatis sub-user toko tsb. */
    public function register(Request $request)
    {
        $store = $request->attributes->get('tenant');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
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
            'parent_agent_id' => $store->id,
        ]);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Registrasi berhasil. Selamat datang!');
    }

    public function logout(Request $request)
    {
        // Mode path: kembali ke halaman login tokonya sendiri (akun agen /
        // pelanggan toko), atau ke /login utama (superadmin & user umum).
        // Mode subdomain: sesuai subdomain tempat logout dilakukan.
        $user = $request->user();
        $sub = Tenancy::isPathMode()
            ? ($user ? Tenancy::storeOf($user)?->subdomain : null)
            : Tenancy::subdomainFromHost($request->getHost());

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Halaman toko -> login toko. Domain utama -> portal.
        return redirect()->away(Tenancy::url($sub, '/login', $request));
    }
}
