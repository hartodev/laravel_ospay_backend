@extends('layouts.app')
@section('title', 'Detail Transaksi')

@section('content')
@include('partials.flow', ['step' => $transaction->status === 'success' ? 5 : 4])
@php
    $awaitingPayment = $transaction->isMidtransPayment() && $transaction->isPaymentPending();
    $inProgress = in_array($transaction->status, ['pending', 'processing'], true) && ! $awaitingPayment;
@endphp

<div class="grid lg:grid-cols-3 gap-6">
    @php
        // Ikon status besar di tengah kartu, seperti layar Detail Transaksi di mobile
        [$ring, $solid, $statusIcon] = match (strtolower((string) $transaction->status)) {
            'success' => ['bg-emerald-50', 'bg-emerald-600', 'check'],
            'pending', 'processing' => ['bg-amber-50', 'bg-amber-500', 'clock'],
            'failed', 'expired', 'cancelled' => ['bg-red-50', 'bg-red-500', 'x'],
            default => ['bg-slate-100', 'bg-slate-400', 'receipt'],
        };
        $rows = [
            ['Ref ID', $transaction->ref_id, true],
            ['Produk', $transaction->product?->name . ' (' . $transaction->product?->product_code . ')', false],
            ['Total Bayar', 'Rp ' . number_format($transaction->sell_price, 0, ',', '.'), false],
            ['Metode bayar', $transaction->payment_method === 'wallet' ? 'Wallet' : 'QRIS (Midtrans)', false],
            ['Waktu', $transaction->created_at->format('d M Y H:i'), false],
        ];
    @endphp

    <div class="lg:col-span-2 card p-6 sm:p-8">
        <div class="text-center">
            <span class="mx-auto grid size-28 place-items-center rounded-full {{ $ring }}">
                <span class="grid size-[4.5rem] place-items-center rounded-full {{ $solid }} text-white">@include('partials.icon', ['name' => $statusIcon, 'class' => 'size-9', 'sw' => 2.5])</span>
            </span>
            <div class="mt-4">@include('partials.badge', ['status' => $transaction->status])</div>
            <h2 class="mt-3 text-xl font-bold tracking-tight text-slate-900">{{ $transaction->product?->name }}</h2>
            <p class="text-slate-500">{{ $transaction->customer_id }}</p>
        </div>

        <div class="my-6 border-t border-dashed border-slate-200"></div>

        <dl class="space-y-4 text-sm">
            @foreach ($rows as [$label, $value, $copy])
                <div class="flex items-start justify-between gap-4">
                    <dt class="shrink-0 text-slate-500">{{ $label }}</dt>
                    <dd class="flex items-center justify-end gap-2 text-right font-semibold text-slate-900 break-all">
                        {{ $value }}
                        @if ($copy)
                            <button type="button" data-copy="{{ $value }}" aria-label="Salin {{ $label }}" class="grid size-7 shrink-0 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-brand-600">
                                <span data-ico>@include('partials.icon', ['name' => 'copy', 'class' => 'size-4'])</span>
                                <span data-done class="hidden text-emerald-600">@include('partials.icon', ['name' => 'check', 'class' => 'size-4', 'sw' => 2.5])</span>
                            </button>
                        @endif
                    </dd>
                </div>
            @endforeach

            @if ($transaction->payment_status)
                <div class="flex items-center justify-between gap-4"><dt class="text-slate-500">Status bayar</dt><dd>@include('partials.badge', ['status' => $transaction->payment_status])</dd></div>
            @endif

            @if ($transaction->sn)
                <div class="flex items-start justify-between gap-4">
                    <dt class="shrink-0 text-slate-500">Serial Number</dt>
                    <dd class="flex items-center justify-end gap-2 text-right font-mono font-semibold text-slate-900 break-all">
                        {{ $transaction->sn }}
                        <button type="button" data-copy="{{ $transaction->sn }}" aria-label="Salin Serial Number" class="grid size-7 shrink-0 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-brand-600">
                            <span data-ico>@include('partials.icon', ['name' => 'copy', 'class' => 'size-4'])</span>
                            <span data-done class="hidden text-emerald-600">@include('partials.icon', ['name' => 'check', 'class' => 'size-4', 'sw' => 2.5])</span>
                        </button>
                    </dd>
                </div>
            @endif
            @if ($transaction->failure_reason)
                <div class="flex items-start justify-between gap-4"><dt class="shrink-0 text-slate-500">Alasan gagal</dt><dd class="text-right font-semibold text-red-700">{{ $transaction->failure_reason }}</dd></div>
            @endif
            @if ($transaction->refund_status && $transaction->refund_status !== 'not_required')
                <div class="flex items-center justify-between gap-4"><dt class="text-slate-500">Refund</dt>
                    <dd class="text-right">@include('partials.badge', ['status' => $transaction->refund_status])
                        @if ($transaction->refund_amount) &nbsp;<span class="font-semibold">Rp {{ number_format($transaction->refund_amount, 0, ',', '.') }}</span> @endif
                    </dd>
                </div>
            @endif
        </dl>
    </div>

    <div class="space-y-4">
        @if ($awaitingPayment && $transaction->qr_url)
            <div class="card p-5 text-center">
                <div class="font-semibold mb-2">Scan QRIS untuk membayar</div>
                <img src="{{ $transaction->qr_url }}" alt="QRIS" class="mx-auto w-56 h-56 object-contain">
                <div class="text-xs text-slate-500 mt-2">Halaman ini diperbarui otomatis setelah pembayaran diterima.</div>
            </div>
        @endif

        @if ($inProgress || $awaitingPayment)
            <form method="POST" action="{{ route('transactions.refresh', $transaction) }}">
                @csrf
                <button class="btn-secondary w-full">Cek status sekarang</button>
            </form>
        @endif

        <a href="{{ route('transactions.index') }}" class="link flex items-center justify-center gap-1 text-sm">@include('partials.icon', ['name' => 'back', 'class' => 'size-4']) Semua transaksi</a>
    </div>
</div>

@push('scripts')
    <script>
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
        {{-- Reload tiap 15 detik, maksimal 8 kali (~2 menit). Tiap reload memicu cek status (dibatasi 10 detik di server). --}}
        <script>
            (() => {
                const key = 'trx-reload-{{ $transaction->id }}';
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
@endsection
