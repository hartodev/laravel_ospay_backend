<?php

namespace App\Http\Middleware;

use App\Services\ThemeService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ShareAppTheme
{
    public function handle(Request $request, Closure $next)
    {
        View::share('theme', ThemeService::current());

        return $next($request);
    }
}
