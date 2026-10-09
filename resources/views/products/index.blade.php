@extends('layouts.app')
@section('title', (request('type') || request('q') || request('brand')) ? 'Produk' : 'Semua Layanan')

@section('content')
@php
    $q = fn (array $extra) => request()->fullUrlWithQuery($extra + ['page' => null]);
    // Tanpa jenis/pencarian: tampilkan pusat kategori. Dengan jenis: daftar produk + filter.
    $hub = ! request('type') && ! request('q') && ! request('brand');
@endphp

@include('partials.flow', ['step' => $hub ? 1 : 2])

@unless ($isPln && ! $hub && ! request('q'))
<form method="GET" class="mb-4 flex gap-3">
    @foreach (['type', 'brand', 'kind', 'number'] as $keep)
        @if (request($keep)) <input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}"> @endif
    @endforeach
    <label class="relative flex-1">
        <span class="sr-only">Cari produk</span>
        <span class="pointer-events-none absolute inset-y-0 left-4 grid place-items-center text-slate-400">@include('partials.icon', ['name' => 'search', 'class' => 'size-5'])</span>
        <input name="q" value="{{ request('q') }}" placeholder="Cari nama / operator / kode" class="field pl-12">
    </label>
    <button class="btn-primary px-6">Cari</button>
</form>
@endunless

@if ($hub)
    @include('products._hub')
@else
<a href="{{ route('products.index') }}" class="link mb-4 inline-flex items-center gap-1 text-sm">@include('partials.icon', ['name' => 'back', 'class' => 'size-4']) Semua Layanan</a>

@if ($isPln && ! request('q'))
    @include('products._pln')
@else

{{-- Nomor HP: operator terdeteksi dari awalan, lalu produk difilter ke provider itu --}}
@if ($showNumber)
    <div class="card mb-4 p-4 sm:p-5">
        <label for="nomor" class="label">Nomor HP tujuan</label>
        <input id="nomor" type="tel" inputmode="numeric" autocomplete="off" value="{{ request('number') }}"
               placeholder="contoh: 0812 3456 7890" class="field max-w-sm">
        <p id="operator" class="mt-2 min-h-5 text-sm text-slate-500" aria-live="polite">Ketik 4 digit awal, produk otomatis disaring sesuai operator.</p>
    </div>
@endif

{{-- Baris 1: jenis produk --}}
<div class="-mx-4 mb-3 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0">
    <a href="{{ $q(['type' => null, 'brand' => null, 'kind' => null, 'number' => null]) }}" class="chip {{ request('type') ? '' : 'chip-on' }}">Semua</a>
    @foreach ($types as $t)
        <a href="{{ $q(['type' => $t, 'brand' => null, 'kind' => null, 'number' => null]) }}" class="chip capitalize {{ request('type') === $t ? 'chip-on' : '' }}">{{ $t }}</a>
    @endforeach
</div>

{{-- Baris 2: provider (Pulsa/Data) atau nama game --}}
@if ($brands->count() > 1)
    <div class="mb-3 flex flex-wrap items-center gap-2">
        <span class="mr-1 text-sm font-medium text-slate-500">{{ $brandLabel }}</span>
        <a href="{{ $q(['brand' => null, 'number' => null]) }}" class="chip {{ request('brand') ? '' : 'chip-on' }}">Semua</a>
        @foreach ($brands as $b)
            <a href="{{ $q(['brand' => $b->brand, 'number' => null]) }}" class="chip {{ request('brand') === $b->brand ? 'chip-on' : '' }}">{{ $b->brand }} <span class="ml-1.5 text-xs opacity-70">{{ $b->total }}</span></a>
        @endforeach
    </div>
@endif

{{-- Baris 3: PLN pra bayar / pasca bayar --}}
@if ($isPln)
    <div class="mb-3 flex flex-wrap items-center gap-2">
        <span class="mr-1 text-sm font-medium text-slate-500">Jenis</span>
        <a href="{{ $q(['kind' => null]) }}" class="chip {{ $kind ? '' : 'chip-on' }}">Semua</a>
        <a href="{{ $q(['kind' => 'prepaid']) }}" class="chip {{ $kind === 'prepaid' ? 'chip-on' : '' }}">Pra Bayar <span class="ml-1.5 text-xs opacity-70">{{ $kindCounts['prepaid'] ?? 0 }}</span></a>
        <a href="{{ $q(['kind' => 'postpaid']) }}" class="chip {{ $kind === 'postpaid' ? 'chip-on' : '' }}">Pasca Bayar <span class="ml-1.5 text-xs opacity-70">{{ $kindCounts['postpaid'] ?? 0 }}</span></a>
    </div>
@endif

<div class="mb-3"></div>

@if ($products->isEmpty())
    <div class="card p-12 text-center text-slate-500">
        @if ($kind === 'postpaid') Belum ada produk pasca bayar untuk saat ini.
        @else Produk tidak ditemukan. @endif
    </div>
@endif

@if ($gridMode)
    {{-- Grid kartu: PLN, Voucher, Game, eSIM --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
        @foreach ($products as $p)
            <a href="{{ route('transactions.create', array_filter(['product' => $p->product_code, 'number' => request('number')])) }}"
               class="card group flex flex-col p-4 transition hover:border-brand-300 hover:shadow-lg hover:shadow-brand-500/10">
                <span class="grid size-12 place-items-center rounded-2xl bg-brand-50 text-brand-600">@include('partials.icon', ['for' => $p->type . ' ' . $p->name, 'class' => 'size-6'])</span>
                <span class="mt-3 line-clamp-2 min-h-[2.5rem] text-sm font-semibold text-slate-900">{{ $p->name }}</span>
                <span class="mt-1 truncate text-xs text-slate-500">{{ $p->brand }}</span>
                @if ($isPln)
                    <span class="mt-2 w-fit rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $p->category === 'postpaid' ? 'bg-teal-50 text-teal-700' : 'bg-brand-50 text-brand-700' }}">{{ $p->category === 'postpaid' ? 'Pasca Bayar' : 'Pra Bayar' }}</span>
                @endif
                <span class="mt-auto pt-3 font-bold text-brand-600">Rp {{ number_format($p->priceForRole($role), 0, ',', '.') }}</span>
            </a>
        @endforeach
    </div>
@else
    {{-- Daftar baris: Pulsa, Data, dan lainnya --}}
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($products as $p)
            <a href="{{ route('transactions.create', array_filter(['product' => $p->product_code, 'number' => request('number')])) }}"
               class="card group flex items-center gap-4 p-4 transition hover:border-brand-300 hover:shadow-lg hover:shadow-brand-500/10">
                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-600">@include('partials.icon', ['for' => $p->type . ' ' . $p->name, 'class' => 'size-6'])</span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate font-semibold text-slate-900">{{ $p->name }}</span>
                    <span class="block truncate text-sm text-slate-500 capitalize">{{ $p->type }} &middot; {{ $p->brand }}</span>
                    <span class="block truncate text-xs text-slate-400">{{ $p->product_code }}</span>
                </span>
                <span class="shrink-0 text-right font-bold text-brand-600">Rp {{ number_format($p->priceForRole($role), 0, ',', '.') }}</span>
            </a>
        @endforeach
    </div>
@endif

<div class="mt-6">{{ $products->links() }}</div>
@endif
@endif

@if ($showNumber)
@push('scripts')
<script>
    (() => {
        const input = document.getElementById('nomor'), info = document.getElementById('operator');
        const brands = @json($brands->pluck('brand')->values());
        const current = @json(request('brand'));

        // Awalan nomor -> operator, dan kata kunci untuk mencocokkan dengan nama brand di database.
        const operators = [
            { name: 'Telkomsel', keys: ['telkomsel', 'simpati', 'kartu as', 'loop'], prefix: ['0811','0812','0813','0821','0822','0823','0851','0852','0853'] },
            { name: 'Indosat',   keys: ['indosat', 'im3', 'mentari'],               prefix: ['0814','0815','0816','0855','0856','0857','0858'] },
            { name: 'XL',        keys: ['xl'],                                      prefix: ['0817','0818','0819','0859','0877','0878'] },
            { name: 'Axis',      keys: ['axis'],                                    prefix: ['0831','0832','0833','0838'] },
            { name: 'Tri',       keys: ['tri', 'three', '3'],                       prefix: ['0895','0896','0897','0898','0899'] },
            { name: 'Smartfren', keys: ['smartfren', 'smart'],                      prefix: ['0881','0882','0883','0884','0885','0886','0887','0888','0889'] },
        ];
        const tokens = (s) => s.toLowerCase().split(/[\s\-_.]+/);
        const brandFor = (op) => brands.find((b) => op.keys.some((k) => k.length <= 3 ? tokens(b).includes(k) : b.toLowerCase().includes(k)));

        const normalize = (v) => {
            let d = v.replace(/\D/g, '');
            if (d.startsWith('62')) d = '0' + d.slice(2);
            return d;
        };

        let timer;
        input.addEventListener('input', () => {
            clearTimeout(timer);
            const num = normalize(input.value);
            if (num.length < 4) { info.textContent = 'Ketik 4 digit awal, produk otomatis disaring sesuai operator.'; return; }

            const op = operators.find((o) => o.prefix.includes(num.slice(0, 4)));
            if (!op) { info.textContent = 'Operator tidak dikenali. Pilih provider secara manual di bawah.'; return; }

            const brand = brandFor(op);
            if (!brand) { info.textContent = 'Operator ' + op.name + ', tetapi belum ada produknya.'; return; }

            info.textContent = 'Operator terdeteksi: ' + op.name + ' (' + brand + ')';
            if (brand === current) return;

            timer = setTimeout(() => {
                const url = new URL(location.href);
                url.searchParams.set('brand', brand);
                url.searchParams.set('number', num);
                url.searchParams.delete('page');
                location.href = url.toString();
            }, 500);
        });

        // Setelah halaman dimuat ulang oleh deteksi, lanjutkan mengetik dari akhir nomor.
        if (input.value) {
            input.focus();
            input.setSelectionRange(input.value.length, input.value.length);
            const op = operators.find((o) => o.prefix.includes(normalize(input.value).slice(0, 4)));
            if (op && current) info.textContent = 'Operator terdeteksi: ' + op.name + ' (' + current + ')';
        }
    })();
</script>
@endpush
@endif
@endsection
