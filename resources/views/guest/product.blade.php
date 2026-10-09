@extends('layouts.public')
@section('title', $product->name)

@php
    $isPhone = (bool) preg_match('/pulsa|data|sms|telp|telepon|paket|roaming/i', (string) $product->type);
    $isPln = $product->category === 'prepaid' && preg_match('/pln/i', (string) $product->type);
    $label = $isPhone ? 'Nomor HP tujuan' : ($isPln ? 'Nomor meter (11 digit)' : 'Nomor / ID tujuan');
    $placeholder = $isPhone ? '08xxxxxxxxxx' : ($isPln ? '12345678901' : 'Nomor atau ID pelanggan');
@endphp

@section('content')
<a href="{{ route('guest.catalog', array_filter(['type' => $product->type, 'brand' => $product->brand])) }}" class="link mb-4 inline-flex items-center gap-1 text-sm">@include('partials.icon', ['name' => 'back', 'class' => 'size-4']) Kembali ke produk</a>

<div class="grid gap-6 lg:grid-cols-3">
    <form method="POST" action="{{ route('guest.store', $product->product_code) }}" class="card p-5 sm:p-7 lg:col-span-2 space-y-5"
          onsubmit="this.querySelector('button[type=submit]').disabled = true">
        @csrf

        <div class="flex items-center gap-4">
            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-600">@include('partials.icon', ['for' => $product->type . ' ' . $product->name, 'class' => 'size-7'])</span>
            <div class="min-w-0">
                <h1 class="text-xl font-bold tracking-tight text-slate-900">{{ $product->name }}</h1>
                <p class="text-sm text-slate-500 capitalize">{{ $product->type }} &middot; {{ $product->brand }}</p>
            </div>
        </div>

        @include('partials.input', ['name' => 'customer_id', 'label' => $label, 'required' => true, 'value' => request('number'), 'placeholder' => $placeholder])

        <div class="flex items-center justify-between rounded-2xl bg-paper p-4">
            <span class="text-sm text-slate-500">Total bayar</span>
            <span class="text-2xl font-extrabold tracking-tight text-slate-900">Rp {{ number_format($product->price_guest, 0, ',', '.') }}</span>
        </div>

        <button type="submit" class="btn-primary w-full py-3.5 text-base">Bayar dengan QRIS</button>
        <p class="text-center text-xs text-slate-500">
            Tanpa akun. Setelah ini Anda mendapat <b>kode transaksi</b> untuk memantau pesanan. Simpan kode tersebut.
        </p>
    </form>

    <div class="space-y-4">
        @include('guest._register-cta')
    </div>
</div>
@endsection
