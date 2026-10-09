<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ config('app.name') }}</title>
    @include('partials.head')
</head>
@php
    // Di subdomain toko, merek yang tampil = nama toko; di domain utama = nama aplikasi.
    $brand = isset($tenant) ? $tenant->storeLabel() : config('app.name');
@endphp
<body class="min-h-screen">
<main class="flex min-h-screen items-center justify-center px-4 py-10">
    <div class="grid w-full max-w-4xl overflow-hidden rounded-3xl border border-slate-200/70 bg-white shadow-card md:grid-cols-[.85fr_1.15fr]">

        {{-- Panel merek (kiri) --}}
        <aside class="relative hidden flex-col justify-between overflow-hidden bg-ink-900 p-10 text-white md:flex">
            <div class="pointer-events-none absolute -right-24 -top-24 size-80 rounded-full bg-brand-600/25 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-28 -left-16 size-80 rounded-full bg-brand-500/10 blur-3xl"></div>

            <div class="relative flex items-center gap-3">
                <img src="{{ \App\Support\AppBranding::webLogo() }}" alt="" class="size-10 rounded-xl" width="40" height="40">
                <span class="truncate text-lg font-extrabold tracking-tight">{{ $brand }}</span>
            </div>

            <div class="relative py-16">
                <p class="text-[11px] font-bold uppercase tracking-[.2em] text-brand-300">@yield('eyebrow')</p>
                <h1 class="mt-4 text-3xl font-extrabold leading-tight tracking-tight lg:text-4xl">@yield('headline')</h1>
                <p class="mt-5 text-sm leading-6 text-blue-100/70">@yield('lead')</p>
                @yield('points')
            </div>

            <div class="relative flex items-center gap-2 text-xs text-blue-100/60">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 4.5 3.2 8.2 8 9 4.8-.8 8-4.5 8-9V6l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>
                Data Anda terlindungi.
            </div>
        </aside>

        {{-- Form (kanan) --}}
        <section class="p-7 sm:p-12">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-2.5 md:hidden">
                    <img src="{{ \App\Support\AppBranding::webLogo() }}" alt="" class="size-9 rounded-xl" width="36" height="36">
                    <span class="truncate text-base font-extrabold tracking-tight text-ink-900">{{ $brand }}</span>
                </div>
                <div class="text-sm font-semibold text-slate-400 md:mb-6">@yield('back')</div>
            </div>

            <div class="mt-8 md:mt-0">
                @include('partials.flash')
                <h2 class="text-3xl font-extrabold tracking-tight text-ink-900">@yield('title')</h2>
                <p class="mt-3 text-sm leading-6 text-slate-500">@yield('subtitle')</p>

                <div class="mt-8">@yield('content')</div>
            </div>

            <div class="mt-7 text-center text-xs text-slate-400">@yield('footer')</div>
        </section>
    </div>
</main>
@stack('scripts')
</body>
</html>
