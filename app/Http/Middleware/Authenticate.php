<?php

namespace App\Http\Middleware;

use App\Support\Tenancy;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Belum login -> ke halaman login yang sesuai host:
     * subdomain toko -> /login toko, domain utama -> portal "cari toko".
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : Tenancy::loginUrl($request);
    }
}
