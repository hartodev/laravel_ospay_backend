<?php

namespace App\Http\Middleware;

use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membaca subdomain dari host dan mencari tokonya.
 *
 * - Host domain utama / IP / localhost  -> lanjut tanpa toko.
 * - Host subdomain                       -> toko HARUS ada dan aktif.
 *
 * Toko yang ditemukan tersedia lewat $request->attributes->get('tenant')
 * dan variabel $tenant di semua view.
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $sub = Tenancy::subdomainFromHost($request->getHost());

        // Aplikasi mobile tidak punya subdomain: toko dikirim lewat header X-Tenant.
        if ($sub === null && $request->expectsJson()) {
            $header = strtolower(trim((string) $request->header('X-Tenant')));

            if ($header !== '' && preg_match(Tenancy::pattern(), $header) === 1) {
                $sub = $header;
            }
        }

        if ($sub === null) {
            return $next($request);
        }

        // Parameter {tenant} dari Route::domain() tidak boleh ikut dikirim ke controller.
        $request->route()?->forgetParameter('tenant');
        URL::defaults(['tenant' => $sub]);

        $store = Tenancy::findStore($sub);

        if (! $store) {
            return $this->unavailable('not_found', $sub, 404);
        }

        if ($store->isPending()) {
            return $this->unavailable('pending', $sub, 403);
        }

        if (! $store->isActive()) {
            return $this->unavailable('suspended', $sub, 403);
        }

        $request->attributes->set('tenant', $store);
        app()->instance('tenant', $store);
        View::share('tenant', $store);

        return $next($request);
    }

    private function unavailable(string $reason, string $sub, int $status): Response
    {
        if (request()->expectsJson()) {
            $messages = [
                'not_found' => 'Toko tidak ditemukan.',
                'pending' => 'Toko belum aktif.',
                'suspended' => 'Toko sedang dinonaktifkan.',
            ];

            return response()->json(['message' => $messages[$reason] ?? 'Toko tidak tersedia.'], $status);
        }

        return response()->view('auth.tenant-unavailable', [
            'reason' => $reason,
            'subdomain' => $sub,
        ], $status);
    }
}
