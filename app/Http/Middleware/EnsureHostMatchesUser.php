<?php

namespace App\Http\Middleware;

use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Isolasi antar toko untuk halaman yang butuh login.
 *
 * Mode "path" (satu host, satu sesi untuk semua toko):
 *   hak akses ditentukan oleh AKUN, bukan oleh URL. Agen / pelanggan toko
 *   harus punya toko yang aktif (ResolveTenant memasang tokonya); kalau toko
 *   sudah tidak aktif, sesinya ditutup.
 *
 * Mode "subdomain":
 * - Di subdomain toko : hanya pemilik toko & sub-user-nya. Superadmin atau
 *                       akun toko lain ditolak.
 * - Di domain utama   : superadmin dan user umum (role user tanpa agen).
 *   Cookie sesi bersifat per-host, jadi kasus ini jarang terjadi, tapi tetap
 *   dicek supaya aman kalau SESSION_DOMAIN diisi.
 */
class EnsureHostMatchesUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $store = $request->attributes->get('tenant');

        $allowed = true;

        if (Tenancy::isPathMode()) {
            // Superadmin & user umum tidak terikat toko. Agen / pelanggan toko
            // wajib punya toko aktif yang cocok dengan akunnya.
            if ($user->isAgent() || ($user->isUser() && $user->parent_agent_id)) {
                $allowed = $store && Tenancy::belongsToStore($user, $store);
            }
        } else {
            $sub = Tenancy::subdomainFromHost($request->getHost());

            if ($sub !== null) {
                $allowed = $store && Tenancy::belongsToStore($user, $store);
            } elseif (Tenancy::isMainHost($request->getHost())) {
                // Domain utama: superadmin dan user umum (role user tanpa agen).
                $allowed = $user->isSuperAdmin()
                    || ($user->isUser() && ! $user->parent_agent_id);
            }
        }

        if ($allowed) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Akun ini tidak terdaftar di toko ini.'], 403);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(Tenancy::loginUrl($request))
            ->withErrors(['email' => 'Akun ini tidak terdaftar di toko ini.']);
    }
}
