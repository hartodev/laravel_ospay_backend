@extends('layouts.public')
@section('title', 'Beli tanpa daftar')

@php
    $q = fn (array $over = []) => route('guest.catalog', array_filter(array_merge(request()->query(), $over), fn ($v) => $v !== null && $v !== ''));
@endphp

@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Beli tanpa daftar</h1>
        <p class="mt-1 text-sm text-slate-500">Pilih produk, bayar lewat QRIS, lalu pantau pesanan dengan kode transaksi.</p>
    </div>
    <a href="{{ route('guest.track') }}" class="btn-secondary btn-sm">Lacak pesanan</a>
</div>

<form method="GET" class="mb-4">
    @foreach (['type', 'brand'] as $keep) @if (request($keep)) <input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}"> @endif @endforeach
    <input name="q" value="{{ request('q') }}" placeholder="Cari nama / operator / kode produk" class="field max-w-md">
</form>

@if ($showNumber)
    <div class="card mb-4 p-4 sm:p-5">
        <label for="nomor" class="label">Nomor HP tujuan</label>
        <input id="nomor" type="tel" inputmode="numeric" autocomplete="off" value="{{ request('number') }}"
               placeholder="contoh: 0812 3456 7890" class="field max-w-sm">
        <p id="operator" class="mt-2 min-h-5 text-sm text-slate-500" aria-live="polite">Ketik 4 digit awal, produk otomatis disaring sesuai operator.</p>
    </div>
@endif

{{-- Jenis produk --}}
<div class="-mx-4 mb-3 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0">
    <a href="{{ $q(['type' => null, 'brand' => null, 'jenis' => null, 'number' => null, 'page' => null]) }}" class="chip {{ $type ? '' : 'chip-on' }}">Semua</a>
    @foreach ($types as $t)
        <a href="{{ $q(['type' => $t, 'brand' => null, 'jenis' => null, 'number' => null, 'page' => null]) }}" class="chip capitalize {{ $type === $t ? 'chip-on' : '' }}">{{ $t }}</a>
    @endforeach
</div>

{{-- Provider / game --}}
@if ($brands->count() > 1)
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <span class="mr-1 text-sm font-medium text-slate-500">{{ $brandLabel }}</span>
        <a href="{{ $q(['brand' => null, 'number' => null, 'page' => null]) }}" class="chip {{ $brand ? '' : 'chip-on' }}">Semua</a>
        @foreach ($brands as $b)
            <a href="{{ $q(['brand' => $b->brand, 'number' => null, 'page' => null]) }}" class="chip {{ $brand === $b->brand ? 'chip-on' : '' }}">{{ $b->brand }} <span class="ml-1.5 text-xs opacity-70">{{ $b->total }}</span></a>
        @endforeach
    </div>
@endif

@if ($products->isEmpty())
    <div class="card p-12 text-center text-slate-500">Produk tidak ditemukan.</div>
@elseif ($gridMode)
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
        @foreach ($products as $p)
            <a href="{{ route('guest.product', array_filter(['product' => $p->product_code, 'number' => request('number')])) }}"
               class="card group flex flex-col p-4 transition hover:border-brand-300 hover:shadow-lg hover:shadow-brand-500/10">
                <span class="grid size-12 place-items-center rounded-2xl bg-brand-50 text-brand-600">@include('partials.icon', ['for' => $p->type . ' ' . $p->name, 'class' => 'size-6'])</span>
                <span class="mt-3 line-clamp-2 min-h-[2.5rem] text-sm font-semibold text-slate-900">{{ $p->name }}</span>
                <span class="mt-1 truncate text-xs text-slate-500">{{ $p->brand }}</span>
                <span class="mt-auto pt-3 font-bold text-brand-600">Rp {{ number_format($p->price_guest, 0, ',', '.') }}</span>
            </a>
        @endforeach
    </div>
@else
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($products as $p)
            <a href="{{ route('guest.product', array_filter(['product' => $p->product_code, 'number' => request('number')])) }}"
               class="card group flex items-center gap-4 p-4 transition hover:border-brand-300 hover:shadow-lg hover:shadow-brand-500/10">
                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-600">@include('partials.icon', ['for' => $p->type . ' ' . $p->name, 'class' => 'size-6'])</span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate font-semibold text-slate-900">{{ $p->name }}</span>
                    <span class="block truncate text-sm text-slate-500 capitalize">{{ $p->type }} &middot; {{ $p->brand }}</span>
                </span>
                <span class="shrink-0 text-right font-bold text-brand-600">Rp {{ number_format($p->price_guest, 0, ',', '.') }}</span>
            </a>
        @endforeach
    </div>
@endif

<div class="mt-6">{{ $products->links() }}</div>

<div class="mt-10 grid gap-4 md:grid-cols-2">
    <div class="card p-5 text-sm text-slate-600">
        <div class="font-semibold text-slate-900">Bagaimana caranya?</div>
        <ol class="mt-2 list-decimal space-y-1 pl-5">
            <li>Pilih produk dan isi nomor tujuan.</li>
            <li>Bayar lewat scan QRIS.</li>
            <li>Simpan <b>kode transaksi</b> untuk memantau status pesanan.</li>
        </ol>
    </div>
    @include('guest._register-cta')
</div>
@endsection

@if ($showNumber)
@push('scripts')
<script>
    (() => {
        const input = document.getElementById('nomor'), info = document.getElementById('operator');
        const brands = @json($brands->pluck('brand')->values());
        const current = @json($brand);
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
        const normalize = (v) => { let d = v.replace(/\D/g, ''); if (d.startsWith('62')) d = '0' + d.slice(2); return d; };

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
