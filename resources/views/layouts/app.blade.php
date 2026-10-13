<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    @include('partials.head')
</head>

<body>
    @php
        $u = auth()->user();

        // [label, route, pola aktif, ikon]
        $menu = match (true) {
            $u->isSuperAdmin() => [
                ['Dashboard', 'dashboard', 'dashboard', 'home'],
                ['Agen', 'admin.agents.index', 'admin.agents.*', 'users'],
                ['Produk & Harga', 'admin.products.index', 'admin.products.*', 'tag'],
                ['Aturan Komisi', 'admin.commissions.index', 'admin.commissions.*', 'coins'],
                ['Konten Aplikasi', 'admin.content.index', 'admin.content.*', 'globe'],
                ['Logo & Ikon', 'admin.content.logo', 'admin.content.logo*', 'image'],
                ['Tema Warna', 'admin.theme.edit', 'admin.theme.*', 'palette'],
            ],
            $u->isAgent() => [
                ['Dashboard', 'dashboard', 'dashboard', 'home'],
                ['Produk', 'products.index', 'products.*', 'grid'],
                ['Transaksi', 'transactions.index', 'transactions.*', 'receipt'],
                ['Wallet', 'agen.wallet', 'agen.wallet', 'wallet'],
                ['Deposit', 'agen.deposits.index', 'agen.deposits.*', 'download'],
                ['Sub-user', 'agen.sub-users.index', 'agen.sub-users.*', 'users'],
                ['Komisi', 'agen.commissions', 'agen.commissions', 'coins'],
            ],
            default => [
                ['Dashboard', 'dashboard', 'dashboard', 'home'],
                ['Produk', 'products.index', 'products.*', 'grid'],
                ['Transaksi', 'transactions.index', 'transactions.*', 'receipt'],
            ],
        };

        $icons = [
            'home' => 'M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9.5Z',
            'users' =>
                'M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1M9.5 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm11.5 8v-1a4 4 0 0 0-3-3.87M15 4.13a3.5 3.5 0 0 1 0 6.74',
            'tag' => 'M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8ZM7.5 7.5h.01',
            'grid' => 'M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z',
            'receipt' => 'M6 3h12v18l-3-2-3 2-3-2-3 2V3Zm3 5h6M9 12h6',
            'wallet' =>
                'M3 7a2 2 0 0 1 2-2h13v4M3 7v10a2 2 0 0 0 2 2h14a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1H5a2 2 0 0 1-2-1Zm13 6h.01',
            'download' => 'M12 3v12m0 0-4-4m4 4 4-4M4 20h16',
            'globe' =>
                'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c2.5-2.4 4-5.6 4-9s-1.5-6.6-4-9m0 18c-2.5-2.4-4-5.6-4-9s1.5-6.6 4-9M3 12h18',
            'coins' =>
                'M12 8c4.4 0 8-1.3 8-3s-3.6-3-8-3-8 1.3-8 3 3.6 3 8 3Zm8 0v4c0 1.7-3.6 3-8 3s-8-1.3-8-3V8m16 4v4c0 1.7-3.6 3-8 3s-8-1.3-8-3v-4',
            'image' => 'M4 5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5Zm0 11 4.5-4.5a1 1 0 0 1 1.4 0L15 17m-2-2 1.6-1.6a1 1 0 0 1 1.4 0L20 16.5M9 8.5h.01',
            'palette' =>
                'M13.5 6.5h.01M17.5 10.5h.01M8.5 7.5h.01M6.5 12.5h.01M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.9 0 1.5-.7 1.5-1.5 0-.4-.1-.7-.4-1-.2-.3-.4-.6-.4-1 0-.8.7-1.5 1.5-1.5H16c3.3 0 6-2.7 6-6 0-4.9-4.5-9-10-9Z',
        ];
        $initial = mb_strtoupper(mb_substr($u->name, 0, 1));
    @endphp

    {{-- Sidebar (desktop) --}}
    <aside class="hidden lg:flex fixed inset-y-0 left-0 w-64 flex-col bg-ink-900 text-slate-300">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-6 h-16 border-b border-white/5">
            <img src="{{ \App\Support\AppBranding::webLogo() }}" alt="{{ config('app.name') }}" class="size-9 rounded-xl"
                width="40" height="40">
            <span class="text-white font-bold text-lg tracking-tight">{{ config('app.name') }}</span>
        </a>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 text-sm">
            @foreach ($menu as [$label, $route, $pattern, $icon])
                @php $active = request()->routeIs($pattern)
                    && ! ($route === 'admin.content.index' && request()->routeIs('admin.content.logo*')); @endphp
                <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif
                    class="group flex items-center gap-3 rounded-2xl px-3 py-2.5 font-medium transition-colors {{ $active ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/30' : 'hover:bg-white/5 hover:text-white' }}">
                    <svg class="size-5 shrink-0 {{ $active ? 'text-white' : 'text-blue-200/60 group-hover:text-blue-100' }}"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="{{ $icons[$icon] }}" />
                    </svg>
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="border-t border-white/5 p-3">
            <a href="{{ route('profile.edit') }}"
                class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-white/5">
                <span
                    class="grid place-items-center size-9 rounded-full bg-brand-500 text-white text-sm font-bold">{{ $initial }}</span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-semibold text-white">{{ $u->name }}</span>
                    <span class="block text-xs text-slate-400 capitalize">{{ $u->role }}</span>
                </span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button
                    class="w-full rounded-xl px-3 py-2 text-left text-sm text-slate-400 hover:bg-white/5 hover:text-white">Keluar</button>
            </form>
        </div>
    </aside>

    {{-- Top bar (mobile) --}}
    <header class="lg:hidden sticky top-0 z-30 bg-ink-900 text-white">
        <div class="flex items-center justify-between px-4 h-14">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-bold">
                <img src="{{ \App\Support\AppBranding::webLogo() }}" alt="{{ config('app.name') }}" class="size-8 rounded-lg"
                    width="40" height="40">
                {{ config('app.name') }}
            </a>
            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('profile.edit') }}" class="text-slate-300">{{ $u->name }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button
                        class="text-slate-400 hover:text-white">Keluar</button></form>
            </div>
        </div>
        <nav class="flex gap-1 overflow-x-auto px-3 pb-2 text-sm">
            @foreach ($menu as [$label, $route, $pattern, $icon])
                <a href="{{ route($route) }}"
                    class="whitespace-nowrap rounded-full px-3.5 py-1.5 font-medium {{ request()->routeIs($pattern) && ! ($route === 'admin.content.index' && request()->routeIs('admin.content.logo*')) ? 'bg-brand-500 text-white' : 'text-slate-300 hover:bg-white/10' }}">{{ $label }}</a>
            @endforeach
        </nav>
    </header>

    <main class="pb-24 lg:pb-0 lg:pl-64">
        <div class="mx-auto max-w-6xl px-4 sm:px-8 py-6 sm:py-10">
            @include('partials.flash')

            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 mb-6 sm:mb-8">
                {{ trim($__env->yieldContent('heading') ?: $__env->yieldContent('title')) }}</h1>

            @yield('content')
        </div>
    </main>

    {{-- Bottom bar (layar kecil), 3 tab seperti navigasi mobile: Beranda, Riwayat, Akun --}}
    @php
        $tabs = $u->isSuperAdmin()
            ? [
                ['Beranda', 'dashboard', 'dashboard', 'home'],
                ['Agen', 'admin.agents.index', 'admin.*', 'users'],
                ['Akun', 'profile.edit', 'profile.*', 'users'],
            ]
            : [
                ['Beranda', 'dashboard', 'dashboard', 'home'],
                ['Riwayat', 'transactions.index', 'transactions.*', 'receipt'],
                ['Akun', 'profile.edit', 'profile.*', 'users'],
            ];
    @endphp
    <nav class="lg:hidden fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 backdrop-blur pb-[env(safe-area-inset-bottom)]"
        aria-label="Navigasi utama">
        <div class="mx-auto grid max-w-md grid-cols-3">
            @foreach ($tabs as [$label, $route, $pattern, $icon])
                @php $on = request()->routeIs($pattern) && ! ($route === 'admin.content.index' && request()->routeIs('admin.content.logo*')); @endphp
                <a href="{{ route($route) }}" @if ($on) aria-current="page" @endif
                    class="flex flex-col items-center gap-1 py-2.5 text-xs font-semibold {{ $on ? 'text-brand-600' : 'text-slate-400' }}">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="{{ $icons[$icon] }}" />
                    </svg>
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </nav>

    @stack('scripts')
</body>

</html>
