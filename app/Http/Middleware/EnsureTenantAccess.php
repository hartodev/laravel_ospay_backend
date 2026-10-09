<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dipasang SETELAH auth:sanctum.
 *
 * Kalau request datang dari sebuah toko (tenant), user yang login harus
 * milik toko itu: agennya sendiri, atau user di bawah agen tersebut.
 * Tanpa tenant (situs utama) tidak ada pembatasan tambahan.
 */
class EnsureTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->attributes->get('tenant');
        $user = $request->user();

        if ($tenant && $user && ! $user->belongsToTenant($tenant)) {
            return response()->json([
                'message' => 'Akun ini tidak terdaftar pada toko tersebut.',
            ], 403);
        }

        return $next($request);
    }
}
