<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        $user = $request->user();

        /*
         * Belum login.
         */
        if (! $user) {
            return response()->json([
                'message' =>
                'Unauthenticated.',
            ], 401);
        }

        /*
         * Akun harus aktif.
         *
         * Ini otomatis menolak:
         *
         * pending
         * suspended
         */
        if (! $user->isActive()) {
            return response()->json([
                'message' =>
                'Akun Anda belum aktif atau '
                    . 'sedang dinonaktifkan.',
            ], 403);
        }

        /*
         * Role harus sesuai.
         */
        if (! in_array(
            $user->role,
            $roles,
            true
        )) {
            return response()->json([
                'message' =>
                'Anda tidak memiliki akses '
                    . 'ke resource ini.',
            ], 403);
        }

        return $next($request);
    }
}
