@extends('layouts.app')
@section('title', 'Komisi')

@section('content')
<div class="grid sm:grid-cols-2 gap-4 mb-6 max-w-xl">
    <div class="card p-5">
        <div class="text-sm text-slate-500">Total pending</div>
        <div class="text-2xl font-bold mt-1">Rp {{ number_format($totalPending, 0, ',', '.') }}</div>
    </div>
    <div class="card p-5">
        <div class="text-sm text-slate-500">Total dibayar</div>
        <div class="text-2xl font-bold mt-1">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
    </div>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr><th>Waktu</th><th>Transaksi</th><th>Produk</th><th class="text-right">Komisi</th><th>Status</th></tr>
        </thead>
        <tbody>
        @forelse ($commissions as $c)
            <tr>
                <td class="whitespace-nowrap">{{ $c->created_at->format('d M Y H:i') }}</td>
                <td class="font-mono text-xs">{{ $c->transaction?->ref_id }}</td>
                <td>{{ $c->transaction?->product?->name }}</td>
                <td class="text-right">Rp {{ number_format($c->amount, 0, ',', '.') }}</td>
                <td>@include('partials.badge', ['status' => $c->status])</td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty">Belum ada komisi. Komisi muncul otomatis saat sub-user Anda membeli produk lewat QRIS dan transaksinya berhasil.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $commissions->links() }}</div>
@endsection
