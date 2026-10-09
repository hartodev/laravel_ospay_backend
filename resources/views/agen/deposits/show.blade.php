@extends('layouts.app')
@section('title', 'Detail Deposit')

@section('content')
<div class="grid md:grid-cols-2 gap-6 max-w-3xl">
    <div class="card divide-y divide-slate-100 text-sm">
        <div class="dl-row"><div class="dl-key">No. transaksi</div><div class="font-mono">{{ $deposit->transaction_number }}</div></div>
        <div class="dl-row"><div class="dl-key">Nominal</div><div class="font-medium">Rp {{ number_format($deposit->amount, 0, ',', '.') }}</div></div>
        <div class="dl-row"><div class="dl-key">Status</div>@include('partials.badge', ['status' => $deposit->status])</div>
        <div class="dl-row"><div class="dl-key">Dibuat</div><div>{{ $deposit->created_at->format('d M Y H:i') }}</div></div>
        @if ($deposit->paid_at)
            <div class="dl-row"><div class="dl-key">Dibayar</div><div>{{ $deposit->paid_at->format('d M Y H:i') }}</div></div>
        @endif
    </div>

    @if ($deposit->isPending() && $deposit->qr_url)
        <div class="card p-5 text-center">
            <div class="font-semibold mb-2">Scan QRIS untuk membayar</div>
            <img src="{{ $deposit->qr_url }}" alt="QRIS" class="mx-auto w-56 h-56 object-contain">
            <div class="text-xs text-slate-500 mt-2">Halaman diperbarui otomatis setelah pembayaran diterima.</div>
        </div>
    @endif
</div>

<a href="{{ route('agen.deposits.index') }}" class="inline-block mt-6 text-sm link">&larr; Semua deposit</a>

@if ($deposit->isPending())
    @push('scripts')
        <script>setTimeout(() => location.reload(), 10000);</script>
    @endpush
@endif
@endsection
