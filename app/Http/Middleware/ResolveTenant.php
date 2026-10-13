<?php

namespace App\Http\Middleware;

use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membaca kode toko dari alamat request dan mencari tokonya.
 *
 * Mode "path"      : kode toko = segmen pertama URL (ospay.test/tokoabc/login),
 *                    datang dari parameter route {tenant}.
 * Mode "subdomain" : kode toko = subdomain host (tokoabc.ospay.test).
 *
 * - Alamat domain utama / IP / localhost -> lanjut tanpa toko.
 * - Alamat toko                           -> toko HARUS ada dan aktif.
 *
 * Halaman setelah login (dashboard dst.) tidak membawa kode toko di URL.
 * Di mode path, toko untuk halaman itu diambil dari AKUN yang login
 * (agen = dirinya sendiri, pelanggan = agen induknya).
 *
 * Toko yang ditemukan tersedia lewat $request->attributes->get('tenant')
 * dan variabel $tenant di semua view.
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Tenancy::isPathMode()) {
            $routeSlug = $request->route('tenant');
            $sub = is_string($routeSlug) && $routeSlug !== '' ? strtolower($routeSlug) : null;

            if ($sub !== null) {
                // Dipakai Tenancy::currentSlug() / loginUrl() di middleware lain.
                $request->attributes->set('tenant_slug', $sub);
            }
        } else {
            $sub = Tenancy::subdomainFromHost($request->getHost());
        }

        // Aplikasi mobile tidak punya subdomain: toko dikirim lewat header X-Tenant.
        if ($sub === null && $request->expectsJson()) {
            $header = strtolower(trim((string) $request->header('X-Tenant')));

            if ($header !== '' && preg_match(Tenancy::pattern(), $header) === 1) {
                $sub = $header;
            }
        }

        if ($sub === null) {
            $this->shareStoreOfLoggedInUser($request);

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

    /**
     * Mode path, halaman setelah login: URL tidak membawa kode toko, jadi toko
     * diambil dari akun yang login supaya nama/logo toko tampil di layout.
     * Toko yang tidak aktif sengaja TIDAK dipasang; EnsureHostMatchesUser lalu
     * mengeluarkan akunnya.
     */
    private function shareStoreOfLoggedInUser(Request $request): void
    {
        if (! Tenancy::isPathMode() || $request->expectsJson()) {
            return;
        }

        $user = Auth::guard('web')->user();
        $store = $user ? Tenancy::storeOf($user) : null;

        if (! $store || ! $store->isActive()) {
            return;
        }

        $request->attributes->set('tenant', $store);
        app()->instance('tenant', $store);
        View::share('tenant', $store);
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
