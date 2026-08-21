<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Contoh pemakaian di route:
     * ->middleware('role:superadmin')
     * ->middleware('role:superadmin,agen')   // boleh lebih dari satu role
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Akun Anda sedang tidak aktif. Hubungi superadmin.',
            ], 403);
        }

        if (! in_array($user->role, $roles, true)) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses ke resource ini.',
            ], 403);
        }

        return $next($request);
    }
}