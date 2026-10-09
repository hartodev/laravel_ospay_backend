@extends('layouts.app')
@section('title', 'Wallet')

@section('content')
{{-- Kartu saldo utama + tombol Topup, seperti layar Wallet Saya di mobile --}}
<section class="relative mb-4 overflow-hidden rounded-3xl bg-brand-500 p-6 text-white shadow-lg shadow-brand-500/25 sm:p-8">
    <div class="pointer-events-none absolute -right-12 -top-12 size-44 rounded-full bg-white/10"></div>
    <div class="relative flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-sm text-blue-100">Saldo tersedia</div>
            <div class="mt-1 text-3xl font-extrabold tracking-tight sm:text-4xl">Rp {{ number_format($wallet->available_balance, 0, ',', '.') }}</div>
        </div>
        <a href="{{ route('agen.deposits.create') }}" class="btn bg-white text-brand-700 hover:bg-blue-50 px-5 py-3">
            @include('partials.icon', ['name' => 'download', 'class' => 'size-5']) Topup saldo
        </a>
    </div>
</section>

<div class="mb-8 grid grid-cols-2 gap-3">
    <div class="card card-pad">
        <div class="stat-label">Total saldo</div>
        <div class="stat-value text-xl sm:text-2xl">Rp {{ number_format($wallet->balance, 0, ',', '.') }}</div>
    </div>
    <div class="card card-pad">
        <div class="stat-label">Saldo ditahan</div>
        <div class="stat-value text-xl sm:text-2xl">Rp {{ number_format($wallet->held_balance, 0, ',', '.') }}</div>
        <div class="mt-1 text-xs text-slate-400">Transaksi pending</div>
    </div>
</div>

<h2 class="mb-3 text-lg font-bold tracking-tight text-slate-900">Mutasi saldo</h2>

{{-- Layar kecil: kartu baris seperti riwayat. Layar lebar: tabel lengkap dipertahankan. --}}
<div class="grid gap-3 lg:hidden">
    @forelse ($ledger as $l)
        <div class="card flex items-center gap-4 p-4">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">@include('partials.badge', ['status' => $l->type])</div>
                <div class="mt-1 truncate text-sm text-slate-600">{{ $l->description }}</div>
                <div class="text-xs text-slate-400">{{ $l->created_at->format('d M Y H:i') }}</div>
            </div>
            <div class="shrink-0 text-right">
                <div class="font-bold text-slate-900">Rp {{ number_format($l->amount, 0, ',', '.') }}</div>
                <div class="text-xs text-slate-400">Saldo {{ number_format($l->balance_after, 0, ',', '.') }}</div>
            </div>
        </div>
    @empty
        <div class="card p-12 text-center text-slate-500">Belum ada mutasi.</div>
    @endforelse
</div>

<div class="table-wrap hidden lg:block">
    <table class="table">
        <thead>
            <tr><th>Waktu</th><th>Tipe</th><th class="text-right">Jumlah</th><th class="text-right">Saldo sebelum</th><th class="text-right">Saldo sesudah</th><th>Keterangan</th></tr>
        </thead>
        <tbody>
        @forelse ($ledger as $l)
            <tr>
                <td class="whitespace-nowrap">{{ $l->created_at->format('d M Y H:i') }}</td>
                <td>@include('partials.badge', ['status' => $l->type])</td>
                <td class="text-right">Rp {{ number_format($l->amount, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($l->balance_before, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($l->balance_after, 0, ',', '.') }}</td>
                <td>{{ $l->description }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="empty">Belum ada mutasi.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $ledger->links() }}</div>
@endsection
