@extends('layouts.app')
@section('title', 'Dashboard Superadmin')

@section('content')
{{-- Dua hal yang butuh tindakan admin diletakkan paling depan --}}
<div class="grid gap-4 md:grid-cols-2 mb-4">
    <a href="{{ route('admin.agents.index', ['status' => 'pending']) }}"
       class="card card-pad transition-colors {{ $pendingAgents ? 'border-amber-300 bg-amber-50/60 hover:border-amber-400' : 'hover:border-brand-600/40' }}">
        <div class="stat-label">Agen menunggu persetujuan</div>
        <div class="stat-value text-4xl {{ $pendingAgents ? '!text-amber-700' : '' }}">{{ $pendingAgents }}</div>
        <div class="mt-3 text-sm font-semibold {{ $pendingAgents ? 'text-amber-800' : 'text-slate-500' }}">
            {{ $pendingAgents ? 'Tinjau pendaftaran' : 'Tidak ada antrean' }}
        </div>
    </a>
    <a href="{{ route('admin.products.index', ['needs_pricing' => 1, 'status' => 'active']) }}"
       class="card card-pad transition-colors {{ $needsPricing ? 'border-amber-300 bg-amber-50/60 hover:border-amber-400' : 'hover:border-brand-600/40' }}">
        <div class="stat-label">Produk aktif belum diberi margin</div>
        <div class="stat-value text-4xl {{ $needsPricing ? '!text-amber-700' : '' }}">{{ number_format($needsPricing, 0, ',', '.') }}</div>
        <div class="mt-3 text-sm font-semibold {{ $needsPricing ? 'text-amber-800' : 'text-slate-500' }}">
            {{ $needsPricing ? 'Atur harga jual' : 'Semua produk sudah punya margin' }}
        </div>
    </a>
</div>

<div class="grid gap-4 sm:grid-cols-3">
    <div class="card card-pad">
        <div class="stat-label">Agen aktif</div>
        <div class="stat-value">{{ $activeAgents }}</div>
    </div>
    <div class="card card-pad">
        <div class="stat-label">Transaksi hari ini</div>
        <div class="stat-value">{{ $trxToday }}
            <span class="text-sm font-medium text-slate-500">{{ $trxSuccessToday }} sukses</span>
        </div>
    </div>
    <div class="card card-pad">
        <div class="stat-label">Produk aktif</div>
        <div class="stat-value">{{ number_format($activeProducts, 0, ',', '.') }}</div>
    </div>
</div>
@endsection
