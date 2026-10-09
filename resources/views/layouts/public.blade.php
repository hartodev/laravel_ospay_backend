<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ config('app.name') }}</title>
    @include('partials.head')
</head>
<body class="min-h-screen flex flex-col">
    <header class="sticky top-0 z-30 border-b border-slate-200/70 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-3 px-4 sm:px-5">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <img src="{{ \App\Support\AppBranding::webLogo() }}" alt="{{ config('app.name') }}" class="size-9 rounded-xl" width="40" height="40">
                <span class="text-lg font-bold tracking-tight text-slate-900">{{ config('app.name') }}</span>
            </a>
            <nav class="flex items-center gap-1 text-sm font-semibold sm:gap-2">
                <a href="{{ route('guest.catalog') }}" class="rounded-xl px-3 py-2 {{ request()->routeIs('guest.catalog', 'guest.product') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-100' }}">Beli</a>
                <a href="{{ route('guest.track') }}" class="rounded-xl px-3 py-2 {{ request()->routeIs('guest.track', 'guest.status') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-100' }}">Lacak <span class="hidden sm:inline">pesanan</span></a>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary btn-sm ml-1">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="hidden rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100 sm:block">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary btn-sm ml-1">Daftar</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-6 sm:px-5 sm:py-8">
        @include('partials.flash')
        @yield('content')
    </main>

    <footer class="border-t border-slate-200/70 py-6 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} {{ config('app.name') }} &middot; Pulsa, PLN, data, game &amp; PPOB
    </footer>

    @stack('scripts')
</body>
</html>
