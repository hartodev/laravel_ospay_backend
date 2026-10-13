<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Helper multi-toko (mode "path" atau "subdomain", lihat config/tenancy.php).
 * Toko = akun agen (users.role = 'agen') yang punya kode toko di kolom
 * users.subdomain.
 */
class Tenancy
{
    public static function mainDomain(): string
    {
        return strtolower((string) config('tenancy.main_domain'));
    }

    /** Mode "path": toko di ospay.test/tokoabc (bukan tokoabc.ospay.test). */
    public static function isPathMode(): bool
    {
        return config('tenancy.mode', 'path') !== 'subdomain';
    }

    /**
     * Kode toko yang ada di ALAMAT request ini (bukan dari akun yang login).
     * Mode path: segmen pertama URL; mode subdomain: subdomain dari host.
     * Null = domain utama.
     */
    public static function currentSlug(Request $request): ?string
    {
        if (self::isPathMode()) {
            $slug = $request->attributes->get('tenant_slug');

            return is_string($slug) && $slug !== '' ? $slug : null;
        }

        return self::subdomainFromHost($request->getHost());
    }

    /** Toko dari sebuah akun: agen = dirinya sendiri, pelanggan = agen induknya. */
    public static function storeOf(User $user): ?User
    {
        if ($user->role === 'agen') {
            return $user;
        }

        return $user->role === 'user' && $user->parent_agent_id ? $user->parentAgent : null;
    }

    /** Alamat toko tanpa skema: "ospay.test/tokoabc" atau "tokoabc.ospay.test". */
    public static function hostFor(string $slug): string
    {
        return self::isPathMode()
            ? self::mainDomain() . '/' . $slug
            : $slug . '.' . self::mainDomain();
    }

    /** Pola untuk Route::where('tenant', ...) (tanpa anchor ^ $). */
    public static function routePattern(): string
    {
        $min = max(2, (int) config('tenancy.min_length', 3)) - 2;
        $max = max($min + 1, (int) config('tenancy.max_length', 30) - 2);

        return '[a-z0-9][a-z0-9-]{' . $min . ',' . $max . '}[a-z0-9]';
    }

    /**
     * Subdomain dari host request, atau null kalau host = domain utama
     * (atau host lain seperti IP / localhost).
     */
    public static function subdomainFromHost(string $host): ?string
    {
        $host = strtolower($host);
        $main = self::mainDomain();

        if ($host === $main || ! str_ends_with($host, '.' . $main)) {
            return null;
        }

        $sub = substr($host, 0, -strlen('.' . $main));

        return ($sub === '' || $sub === 'www') ? null : $sub;
    }

    public static function isMainHost(string $host): bool
    {
        return strtolower($host) === self::mainDomain();
    }

    /**
     * URL absolut ke domain utama / halaman toko, ikut scheme & port request
     * (penting saat dev memakai php artisan serve --port=xxxx).
     *
     * Mode path      : url('tokoabc', '/login') -> https://ospay.test/tokoabc/login
     * Mode subdomain : url('tokoabc', '/login') -> https://tokoabc.ospay.test/login
     */
    public static function url(?string $sub, string $path = '/', ?Request $request = null): string
    {
        $request ??= request();

        $port = (int) $request->getPort();
        $port = in_array($port, [80, 443], true) ? '' : ':' . $port;
        $path = ltrim($path, '/');

        if (self::isPathMode()) {
            $prefix = $sub ? $sub . '/' : '';
            $url = $request->getScheme() . '://' . self::mainDomain() . $port . '/' . $prefix . $path;

            return $sub ? rtrim($url, '/') : $url;
        }

        $host = $sub ? $sub . '.' . self::mainDomain() : self::mainDomain();

        return $request->getScheme() . '://' . $host . $port . '/' . $path;
    }

    /** URL halaman login yang sesuai alamat request saat ini. */
    public static function loginUrl(?Request $request = null): string
    {
        $request ??= request();

        return self::url(self::currentSlug($request), '/login', $request);
    }

    public static function findStore(string $sub): ?User
    {
        return User::query()
            ->where('role', 'agen')
            ->where('subdomain', strtolower($sub))
            ->first();
    }

    /** Akun boleh login di toko ini = pemilik toko atau sub-user-nya. */
    public static function belongsToStore(User $user, User $store): bool
    {
        if (! in_array($user->role, ['agen', 'user'], true)) {
            return false;
        }

        return $user->id === $store->id || (int) $user->parent_agent_id === $store->id;
    }

    public static function isReserved(string $sub): bool
    {
        return in_array(strtolower($sub), config('tenancy.reserved', []), true);
    }

    /** Aturan format subdomain: huruf kecil, angka, minus; tidak diawali/diakhiri minus. */
    public static function pattern(): string
    {
        $min = max(2, (int) config('tenancy.min_length', 3)) - 2;
        $max = max($min + 1, (int) config('tenancy.max_length', 30) - 2);

        return '/^[a-z0-9][a-z0-9-]{' . $min . ',' . $max . '}[a-z0-9]$/';
    }

    public static function slugify(string $text): string
    {
        $slug = Str::slug($text);
        $slug = substr($slug, 0, (int) config('tenancy.max_length', 30));

        return trim($slug, '-');
    }

    /** Subdomain unik dari sebuah nama (dipakai kalau agen belum punya subdomain). */
    public static function uniqueSubdomain(string $name): string
    {
        $base = self::slugify($name);

        if (strlen($base) < (int) config('tenancy.min_length', 3)) {
            $base = 'toko-' . ($base !== '' ? $base : Str::lower(Str::random(4)));
        }

        $max = (int) config('tenancy.max_length', 30);
        $candidate = substr($base, 0, $max);
        $i = 2;

        while (self::isReserved($candidate) || User::where('subdomain', $candidate)->exists()) {
            $suffix = '-' . $i++;
            $candidate = substr($base, 0, $max - strlen($suffix)) . $suffix;
        }

        return trim($candidate, '-');
    }
}
