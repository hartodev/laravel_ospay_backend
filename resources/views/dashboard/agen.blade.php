@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
{{-- Alamat toko agen --}}
@if (auth()->user()->subdomain)
    <div class="mb-5 flex flex-wrap items-center justify-between gap-2 rounded-2xl border border-brand-100 bg-brand-50 px-4 py-3 text-sm text-brand-800">
        <span>Alamat toko Anda: <strong>{{ auth()->user()->storeHost() }}</strong></span>
        <button type="button" class="font-semibold underline"
                onclick="navigator.clipboard.writeText('{{ \App\Support\Tenancy::url(auth()->user()->subdomain, '/register') }}'); this.textContent = 'Tersalin'">
            Salin link daftar pelanggan
        </button>
    </div>
@endif
{{-- Kartu saldo: elemen utama beranda --}}
@php $saldo = 'Rp ' . number_format($wallet->available_balance, 0, ',', '.'); @endphp
<section class="card mb-8 overflow-hidden">
    <div class="flex items-start justify-between gap-4 p-6 sm:p-8">
        <div class="min-w-0">
            <div class="text-sm text-slate-500">Saldo Anda</div>
            <div id="saldo" data-saldo="{{ $saldo }}" class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">{{ $saldo }}</div>
            @if ($wallet->held_balance > 0)
                <div class="mt-2 text-sm text-amber-700">Ditahan untuk transaksi pending: Rp {{ number_format($wallet->held_balance, 0, ',', '.') }}</div>
            @endif
        </div>
        <button type="button" id="toggle-saldo" aria-label="Sembunyikan saldo" aria-pressed="false"
                class="grid size-11 shrink-0 place-items-center rounded-2xl text-slate-400 hover:bg-slate-50 hover:text-slate-700">
            <span data-on>@include('partials.icon', ['name' => 'eye', 'class' => 'size-5'])</span>
            <span data-off class="hidden">@include('partials.icon', ['name' => 'eye-off', 'class' => 'size-5'])</span>
        </button>
    </div>

    <div class="grid grid-cols-4 gap-2 border-t border-slate-100 px-4 py-5 sm:max-w-xl sm:px-8">
        @foreach ([
            ['Topup', route('agen.deposits.create'), 'download'],
            ['Dompet', route('agen.wallet'), 'wallet'],
            ['Riwayat', route('transactions.index'), 'receipt'],
            ['Layanan', route('products.index'), 'grid'],
        ] as [$label, $url, $icon])
            <a href="{{ $url }}" class="group flex flex-col items-center gap-2 text-sm text-slate-600">
                <span class="tile size-14">@include('partials.icon', ['name' => $icon, 'class' => 'size-6'])</span>
                {{ $label }}
            </a>
        @endforeach
    </div>
</section>

<div class="grid gap-8 lg:grid-cols-3">
    <div class="space-y-8 lg:col-span-2">
        <section>@include('partials.services')</section>

        <section>@include('partials.banners', ['isAgen' => true])</section>

        <section>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold tracking-tight text-slate-900">Transaksi terakhir</h2>
                <a href="{{ route('transactions.index') }}" class="link text-sm">Lihat semua</a>
            </div>
            @include('transactions._table', ['transactions' => $latest, 'cols' => ''])
        </section>
    </div>

    <aside class="space-y-4">
        <h2 class="text-lg font-bold tracking-tight text-slate-900">Ringkasan</h2>
        <div class="card card-pad">
            <div class="stat-label">Komisi pending</div>
            <div class="stat-value">Rp {{ number_format($pendingCommission, 0, ',', '.') }}</div>
        </div>
        <div class="card card-pad">
            <div class="stat-label">Komisi dibayar</div>
            <div class="stat-value">Rp {{ number_format($paidCommission, 0, ',', '.') }}</div>
        </div>
        <a href="{{ route('agen.sub-users.index') }}" class="card card-pad block transition hover:border-brand-300">
            <div class="stat-label">Sub-user</div>
            <div class="stat-value">{{ $subUserCount }}</div>
        </a>
    </aside>
</div>

@push('scripts')
<script>
    (() => {
        const btn = document.getElementById('toggle-saldo'), el = document.getElementById('saldo');
        btn.addEventListener('click', () => {
            const hide = btn.getAttribute('aria-pressed') !== 'true';
            btn.setAttribute('aria-pressed', hide);
            btn.setAttribute('aria-label', hide ? 'Tampilkan saldo' : 'Sembunyikan saldo');
            el.textContent = hide ? 'Rp ••••••' : el.dataset.saldo;
            btn.querySelector('[data-on]').classList.toggle('hidden', hide);
            btn.querySelector('[data-off]').classList.toggle('hidden', !hide);
        });
    })();
</script>
@endpush
@endsection
