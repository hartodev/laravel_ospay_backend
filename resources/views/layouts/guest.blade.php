<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ config('app.name') }}</title>
    @include('partials.head')
</head>
<body class="min-h-screen lg:grid lg:grid-cols-[minmax(0,5fr)_minmax(0,6fr)]">
    {{-- Panel merek --}}
    <section class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-ink-900 p-12 text-white">
        <div class="pointer-events-none absolute -right-24 -top-24 size-96 rounded-full bg-brand-600/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-16 size-96 rounded-full bg-brand-500/10 blur-3xl"></div>

        <div class="relative flex items-center gap-3">
            <img src="{{ \App\Support\AppBranding::webLogo() }}" alt="{{ config('app.name') }}" class="size-10 rounded-xl" width="40" height="40">
            <span class="text-xl font-bold tracking-tight">{{ config('app.name') }}</span>
        </div>

        <div class="relative max-w-md">
            <h2 class="text-4xl font-extrabold leading-tight tracking-tight">Jual pulsa, token PLN, dan paket data dari satu tempat.</h2>
            <p class="mt-4 text-slate-300 leading-relaxed">Saldo, komisi, dan riwayat transaksi tercatat rapi, jadi Anda tahu persis berapa untung hari ini.</p>
        </div>

        <p class="relative text-sm text-slate-400">Pulsa, PLN, data, game &amp; PPOB</p>
    </section>

    {{-- Form --}}
    <section class="flex min-h-screen lg:min-h-0 items-center justify-center p-5 sm:p-10">
        <div class="w-full max-w-md">
            <div class="lg:hidden mb-8 flex items-center gap-3">
                <img src="{{ \App\Support\AppBranding::webLogo() }}" alt="{{ config('app.name') }}" class="size-10 rounded-xl" width="40" height="40">
                <span class="text-xl font-bold tracking-tight text-slate-900">{{ config('app.name') }}</span>
            </div>

            <div class="card card-pad sm:p-8">
                @include('partials.flash')
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 mb-6">@yield('title')</h1>
                @yield('content')
            </div>

            <div class="mt-5 text-center text-sm text-slate-500">@yield('footer')</div>
        </div>
    </section>
</body>
</html>
