<?php

namespace App\Http\Middleware;

use Closure;
use App\Support\Tenancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cek login + akun aktif + role.
 *
 * Pemakaian: ->middleware('role:agen,user')
 *
 * - Request web  : redirect ke login / abort 403.
 * - Request JSON : response JSON (sama seperti versi API).
 */
class WebCheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->deny($request, 401, 'Unauthenticated.');
        }

        // Akun harus aktif (otomatis menolak pending & suspended).
        if (! $user->isActive()) {
            if (! $request->expectsJson()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return $this->deny(
                $request,
                403,
                'Akun Anda belum aktif atau sedang dinonaktifkan.'
            );
        }

        if (! in_array($user->role, $roles, true)) {
            return $this->deny($request, 403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }

    private function deny(Request $request, int $status, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        // Belum login / akun tidak aktif -> kembali ke halaman login.
        if ($status === 401 || ! Auth::check()) {
            return redirect(Tenancy::loginUrl($request))->withErrors(['email' => $message]);
        }

        abort($status, $message);
    }
}
