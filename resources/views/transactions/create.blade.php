@extends('layouts.app')
@section('title', 'Beli Produk')

@section('content')
@include('partials.flow', ['step' => 3])
<div class="grid max-w-5xl gap-6 lg:grid-cols-5">
    {{-- Kartu biru ringkasan produk, seperti kartu nomor di layar Pulsa mobile --}}
    <section class="relative overflow-hidden rounded-3xl bg-brand-500 p-6 text-white shadow-lg shadow-brand-500/25 sm:p-8 lg:col-span-2">
        <div class="pointer-events-none absolute -right-12 -top-12 size-44 rounded-full bg-white/10"></div>
        <span class="relative grid size-14 place-items-center rounded-2xl bg-white/20">@include('partials.icon', ['for' => $product->type . ' ' . $product->name, 'class' => 'size-7'])</span>
        <div class="relative mt-5 text-sm capitalize text-blue-100">{{ $product->type }} &middot; {{ $product->brand }}</div>
        <div class="relative mt-1 text-2xl font-extrabold tracking-tight">{{ $product->name }}</div>
        <div class="relative mt-1 text-xs text-blue-100/80">{{ $product->product_code }}</div>
        @if ($product->description)
            <p class="relative mt-3 text-sm leading-relaxed text-blue-50">{{ $product->description }}</p>
        @endif
        <div class="relative mt-6 text-3xl font-extrabold tracking-tight">Rp {{ number_format($price, 0, ',', '.') }}</div>
    </section>

    <section class="card p-6 sm:p-8 lg:col-span-3">
        <form method="POST" action="{{ route('transactions.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="product_code" value="{{ $product->product_code }}">
            @include('partials.input', [
                'name' => 'customer_id',
                'value' => request('number'),
                'label' => 'Nomor HP / ID Pelanggan tujuan',
                'required' => true,
                'placeholder' => 'contoh: 08123456789',
            ])

            <p class="text-sm text-slate-500">
                @if (auth()->user()->isAgent())
                    Pembayaran dipotong dari saldo wallet Anda.
                @else
                    Setelah klik bayar, Anda akan mendapat QRIS untuk dibayar.
                @endif
            </p>

            <div class="flex gap-3">
                <button class="btn-primary px-6 py-3">
                    {{ auth()->user()->isAgent() ? 'Beli sekarang' : 'Lanjut bayar' }}
                </button>
                <a href="{{ route('products.index') }}" class="btn-secondary px-6 py-3">Batal</a>
            </div>
        </form>
    </section>
</div>
@endsection
