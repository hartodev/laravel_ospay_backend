<?php

namespace App\Http\Middleware;

use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Isolasi antar toko untuk halaman yang butuh login:
 *
 * - Di subdomain toko : hanya pemilik toko & sub-user-nya. Superadmin atau
 *                       akun toko lain ditolak.
 * - Di domain utama   : superadmin dan user umum (role user tanpa agen).
 *
 * Cookie sesi bersifat per-host, jadi kasus ini jarang terjadi, tapi tetap
 * dicek supaya aman kalau SESSION_DOMAIN diisi (cookie dibagi antar subdomain).
 */
class EnsureHostMatchesUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $sub = Tenancy::subdomainFromHost($request->getHost());
        $store = $request->attributes->get('tenant');

        $allowed = true;

        if ($sub !== null) {
            $allowed = $store && Tenancy::belongsToStore($user, $store);
        } elseif (Tenancy::isMainHost($request->getHost())) {
            // Domain utama: superadmin dan user umum (role user tanpa agen).
            $allowed = $user->isSuperAdmin()
                || ($user->isUser() && ! $user->parent_agent_id);
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
