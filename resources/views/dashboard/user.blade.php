@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
{{-- Kartu sapaan + shortcut, seperti header Beranda mobile untuk user biasa --}}
<section class="card mb-8 overflow-hidden">
    <div class="p-6 sm:p-8">
        <div class="text-sm text-slate-500">Halo,</div>
        <div class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ auth()->user()->name }}</div>
        <p class="mt-1 text-sm text-slate-500">Mau beli apa hari ini?</p>
    </div>
    <div class="grid grid-cols-4 gap-2 border-t border-slate-100 px-4 py-5 sm:max-w-xl sm:px-8">
        @foreach ([
            ['Pulsa', route('products.index', ['type' => 'pulsa']), 'phone'],
            ['Paket Data', route('products.index', ['type' => 'data']), 'wifi'],
            ['Token PLN', route('products.index', ['type' => 'pln']), 'bolt'],
            ['Riwayat', route('transactions.index'), 'receipt'],
        ] as [$label, $url, $icon])
            <a href="{{ $url }}" class="group flex flex-col items-center gap-2 text-center text-sm text-slate-600">
                <span class="tile size-14">@include('partials.icon', ['name' => $icon, 'class' => 'size-6'])</span>
                {{ $label }}
            </a>
        @endforeach
    </div>
</section>

<div class="grid gap-8 lg:grid-cols-3">
    <div class="space-y-8 lg:col-span-2">
        <section>@include('partials.services')</section>
        <section>@include('partials.banners', ['isAgen' => false])</section>
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
            <div class="stat-label">Total transaksi</div>
            <div class="stat-value">{{ $total }}</div>
        </div>
        <div class="card card-pad">
            <div class="stat-label">Berhasil</div>
            <div class="stat-value">{{ $success }}</div>
        </div>
    </aside>
</div>
@endsection
