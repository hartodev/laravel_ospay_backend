@extends('layouts.public')
@section('title', 'Status pesanan')

@php
    $awaitingPayment = $transaction->isMidtransPayment() && $transaction->isPaymentPending();
    $inProgress = in_array($transaction->status, ['pending', 'processing'], true) && ! $awaitingPayment;
    [$ring, $solid, $statusIcon] = match (strtolower((string) $transaction->status)) {
        'success' => ['bg-emerald-50', 'bg-emerald-600', 'check'],
        'pending', 'processing' => ['bg-amber-50', 'bg-amber-500', 'clock'],
        'failed', 'expired', 'cancelled' => ['bg-red-50', 'bg-red-500', 'x'],
        default => ['bg-slate-100', 'bg-slate-400', 'receipt'],
    };
    $headline = match (true) {
        $awaitingPayment => 'Menunggu pembayaran',
        $transaction->status === 'success' => 'Pesanan berhasil',
        $inProgress => 'Pesanan sedang diproses',
        default => 'Pesanan tidak berhasil',
    };
@endphp

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-6">
        {{-- Kode transaksi --}}
        <div class="card border-brand-200 bg-brand-50/60 p-5">
            <div class="text-xs font-semibold uppercase tracking-wide text-brand-700">Kode transaksi Anda</div>
            <div class="mt-1.5 flex flex-wrap items-center gap-3">
                <span class="break-all font-mono text-lg font-bold tracking-wide text-slate-900 sm:text-xl">{{ $transaction->ref_id }}</span>
                <button type="button" data-copy="{{ $transaction->ref_id }}" class="btn-secondary btn-sm">
                    <span data-ico>Salin kode</span><span data-done class="hidden text-emerald-600">Tersalin</span>
                </button>
            </div>
            <p class="mt-2 text-xs text-slate-600">Simpan kode ini. Dengan kode ini Anda bisa membuka halaman ini lagi lewat menu <b>Lacak pesanan</b>.</p>
        </div>

        <div class="card p-6 sm:p-8">
            <div class="text-center">
                <span class="mx-auto grid size-28 place-items-center rounded-full {{ $ring }}">
                    <span class="grid size-[4.5rem] place-items-center rounded-full {{ $solid }} text-white">@include('partials.icon', ['name' => $statusIcon, 'class' => 'size-9', 'sw' => 2.5])</span>
                </span>
                <div class="mt-4">@include('partials.badge', ['status' => $transaction->status])</div>
                <h1 class="mt-3 text-xl font-bold tracking-tight text-slate-900">{{ $headline }}</h1>
                <p class="mt-1 font-semibold text-slate-700">{{ $transaction->product?->name }}</p>
                <p class="text-slate-500">{{ $transaction->customer_id }}</p>
            </div>

            <div class="my-6 border-t border-dashed border-slate-200"></div>

            <dl class="space-y-4 text-sm">
                <div class="flex items-start justify-between gap-4"><dt class="text-slate-500">Total bayar</dt><dd class="font-semibold text-slate-900">Rp {{ number_format($transaction->sell_price, 0, ',', '.') }}</dd></div>
                <div class="flex items-start justify-between gap-4"><dt class="text-slate-500">Metode bayar</dt><dd class="font-semibold text-slate-900">QRIS</dd></div>
                <div class="flex items-start justify-between gap-4"><dt class="text-slate-500">Waktu pesan</dt><dd class="font-semibold text-slate-900">{{ $transaction->created_at->format('d M Y H:i') }}</dd></div>
                @if ($transaction->payment_status)
                    <div class="flex items-center justify-between gap-4"><dt class="text-slate-500">Status bayar</dt><dd>@include('partials.badge', ['status' => $transaction->payment_status])</dd></div>
                @endif
                @if ($transaction->sn)
                    <div class="flex items-start justify-between gap-4">
                        <dt class="shrink-0 text-slate-500">Serial Number</dt>
                        <dd class="flex items-center justify-end gap-2 break-all text-right font-mono font-semibold text-slate-900">
                            {{ $transaction->sn }}
                            <button type="button" data-copy="{{ $transaction->sn }}" aria-label="Salin Serial Number" class="btn-secondary btn-sm">
                                <span data-ico>Salin</span><span data-done class="hidden text-emerald-600">Tersalin</span>
                            </button>
                        </dd>
                    </div>
                @endif
                @if ($transaction->failure_reason)
                    <div class="flex items-start justify-between gap-4"><dt class="shrink-0 text-slate-500">Keterangan</dt><dd class="text-right font-semibold text-red-700">{{ $transaction->failure_reason }}</dd></div>
                @endif
                @if ($transaction->refund_status && $transaction->refund_status !== 'not_required')
                    <div class="flex items-center justify-between gap-4"><dt class="text-slate-500">Pengembalian dana</dt>
                        <dd class="text-right">@include('partials.badge', ['status' => $transaction->refund_status])
                            @if ($transaction->refund_amount) &nbsp;<span class="font-semibold">Rp {{ number_format($transaction->refund_amount, 0, ',', '.') }}</span> @endif
                        </dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    <div class="space-y-4">
        @if ($awaitingPayment && $transaction->qr_url)
            <div class="card p-5 text-center">
                <div class="mb-2 font-semibold">Scan QRIS untuk membayar</div>
                <img src="{{ $transaction->qr_url }}" alt="QRIS" class="mx-auto h-56 w-56 object-contain">
                <div class="mt-2 text-xs text-slate-500">Buka e-wallet atau m-banking apa pun yang mendukung QRIS. Halaman ini diperbarui otomatis setelah pembayaran diterima.</div>
            </div>
        @endif

        @if ($inProgress || $awaitingPayment)
            <form method="POST" action="{{ route('guest.refresh', $transaction->ref_id) }}">
                @csrf
                <button class="btn-secondary w-full">Cek status sekarang</button>
            </form>
        @endif

        @if (in_array($transaction->status, ['failed', 'expired', 'cancelled'], true))
            <a href="{{ route('guest.product', $transaction->product?->product_code) }}" class="btn-primary w-full">Pesan lagi</a>
        @else
            <a href="{{ route('guest.catalog') }}" class="btn-secondary w-full">Beli produk lain</a>
        @endif

        @include('guest._register-cta')
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Simpan kode di perangkat ini supaya mudah dibuka lagi dari halaman Lacak pesanan.
    try {
        const code = @json($transaction->ref_id), name = @json($transaction->product?->name);
        let list = JSON.parse(localStorage.getItem('ospay_guest_orders') || '[]');
        if (!Array.isArray(list)) list = [];
        list = list.filter((o) => o.code !== code);
        list.unshift({ code, name, at: Date.now() });
        localStorage.setItem('ospay_guest_orders', JSON.stringify(list.slice(0, 10)));
    } catch (e) {}

    document.querySelectorAll('[data-copy]').forEach((btn) => btn.addEventListener('click', async () => {
        try { await navigator.clipboard.writeText(btn.dataset.copy); } catch (e) { return; }
        btn.querySelector('[data-ico]').classList.add('hidden');
        btn.querySelector('[data-done]').classList.remove('hidden');
        setTimeout(() => {
            btn.querySelector('[data-ico]').classList.remove('hidden');
            btn.querySelector('[data-done]').classList.add('hidden');
        }, 1500);
    }));
</script>
@endpush

@if ($awaitingPayment || $inProgress)
    @push('scripts')
        {{-- Muat ulang tiap 15 detik, maksimal 8 kali (~2 menit). Tiap muat memicu cek status (dibatasi 10 detik di server). --}}
        <script>
            (() => {
                const key = 'guest-reload-{{ $transaction->id }}';
                const nav = performance.getEntriesByType('navigation')[0];
                if (!nav || nav.type !== 'reload') sessionStorage.removeItem(key);
                const n = +(sessionStorage.getItem(key) || 0);
                if (n < 8) {
                    sessionStorage.setItem(key, n + 1);
                    setTimeout(() => location.reload(), 15000);
                }
            })();
        </script>
    @endpush
@endif
