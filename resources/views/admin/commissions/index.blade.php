@extends('layouts.app')
@section('title', 'Aturan Komisi')

@section('content')
<div class="grid sm:grid-cols-3 gap-4 mb-6">
    <div class="card p-5">
        <div class="text-sm text-slate-500">Komisi terbentuk</div>
        <div class="text-2xl font-bold mt-1">{{ number_format($totalCommissions, 0, ',', '.') }}</div>
        <div class="text-xs text-slate-400 mt-1">Total Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
    </div>
    <div class="card p-5">
        <div class="text-sm text-slate-500">Transaksi sukses tanpa komisi</div>
        <div class="text-2xl font-bold mt-1">{{ number_format($withoutCommission, 0, ',', '.') }}</div>
        <div class="text-xs text-slate-400 mt-1">Transaksi QRIS sub-user agen</div>
    </div>
    <div class="card p-5">
        <div class="text-sm text-slate-500">Produk aktif tanpa margin</div>
        <div class="text-2xl font-bold mt-1">{{ number_format($noMarginProducts, 0, ',', '.') }}</div>
        <a href="{{ route('admin.products.index', ['needs_pricing' => 1]) }}" class="text-xs link">Atur harga jual &rarr;</a>
    </div>
</div>

@if ($noMarginProducts > 0)
    <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        Komisi tidak bisa lebih besar dari margin (harga jual &minus; harga beli). Produk yang harga usernya belum
        di atas harga beli tidak akan menghasilkan komisi.
    </div>
@endif

<div class="grid md:grid-cols-2 gap-4 mb-6">
    <form method="POST" action="{{ route('admin.commissions.store') }}" class="card p-4 text-sm">
        @csrf
        <div class="font-semibold mb-1">Tambah / ganti aturan</div>
        <p class="text-slate-500 mb-3">Kosongkan kode produk untuk aturan umum (semua produk). Aturan khusus produk didahulukan.</p>
        <div class="grid sm:grid-cols-3 gap-2">
            <input name="product_code" value="{{ old('product_code') }}" placeholder="Kode produk (opsional)" class="field sm:col-span-3">
            <select name="type" class="field">
                <option value="nominal" @selected(old('type') === 'nominal')>Nominal (Rp)</option>
                <option value="percentage" @selected(old('type') === 'percentage')>Persen (%)</option>
            </select>
            <input name="value" type="number" step="0.01" min="0" value="{{ old('value') }}" placeholder="Nilai" required class="field">
            <button class="btn-primary">Simpan</button>
        </div>
        @foreach (['product_code', 'type', 'value'] as $f)
            @error($f)<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        @endforeach
    </form>

    <form method="POST" action="{{ route('admin.commissions.backfill') }}" class="card p-4 text-sm"
          onsubmit="this.querySelector('button').disabled = true">
        @csrf
        <div class="font-semibold mb-1">Hitung dari transaksi yang sudah ada</div>
        <p class="text-slate-500 mb-3">Membuat komisi untuk transaksi sub-user yang sudah sukses tetapi belum punya komisi. Aman dijalankan berulang.</p>
        <button class="btn-secondary" @disabled($withoutCommission === 0)>Hitung sekarang ({{ $withoutCommission }})</button>
    </form>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Berlaku untuk</th>
                <th>Tipe</th>
                <th>Nilai</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse ($rules as $r)
            <tr>
                <td>
                    @if ($r->product)
                        <div class="font-medium">{{ $r->product->name }}</div>
                        <div class="text-xs text-slate-400">{{ $r->product->product_code }}</div>
                    @else
                        <div class="font-medium">Semua produk (umum)</div>
                    @endif
                </td>
                <td>
                    <select form="r{{ $r->id }}" name="type" class="field field-sm">
                        <option value="nominal" @selected($r->type === 'nominal')>Nominal (Rp)</option>
                        <option value="percentage" @selected($r->type === 'percentage')>Persen (%)</option>
                    </select>
                </td>
                <td><input form="r{{ $r->id }}" type="number" step="0.01" min="0" name="value" value="{{ rtrim(rtrim((string) $r->value, '0'), '.') }}" class="field field-sm w-28"></td>
                <td>
                    <select form="r{{ $r->id }}" name="status" class="field field-sm">
                        <option value="active" @selected($r->status === 'active')>active</option>
                        <option value="inactive" @selected($r->status === 'inactive')>inactive</option>
                    </select>
                </td>
                <td class="whitespace-nowrap">
                    <form id="r{{ $r->id }}" method="POST" action="{{ route('admin.commissions.update', $r) }}" class="inline">
                        @csrf @method('PATCH')
                        <button class="btn-primary btn-sm">Simpan</button>
                    </form>
                    <form method="POST" action="{{ route('admin.commissions.destroy', $r) }}" class="inline"
                          onsubmit="return confirm('Hapus aturan ini?')">
                        @csrf @method('DELETE')
                        <button class="btn-secondary btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty">Belum ada aturan komisi. Tambahkan aturan umum di atas agar komisi mulai terbentuk.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $rules->links() }}</div>

{{-- Komisi menunggu pencairan --}}
<div class="mt-8 mb-3 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-bold text-slate-900">Komisi menunggu pencairan</h2>
        <p class="text-sm text-slate-500">Dicairkan = masuk ke saldo wallet agen. Total pending: Rp {{ number_format($pendingTotal, 0, ',', '.') }}</p>
    </div>
    @if ($pendingCommissions->isNotEmpty())
        <form method="POST" action="{{ route('admin.commissions.payout-all') }}" onsubmit="return confirm('Cairkan semua komisi pending?')">
            @csrf
            <button class="btn-primary">Cairkan semua</button>
        </form>
    @endif
</div>
<div class="table-wrap">
    <table class="table">
        <thead><tr><th>Waktu</th><th>Agen</th><th>Produk</th><th class="text-right">Komisi</th><th></th></tr></thead>
        <tbody>
        @forelse ($pendingCommissions as $c)
            <tr>
                <td class="whitespace-nowrap">{{ $c->created_at->format('d M Y H:i') }}</td>
                <td>{{ $c->agent?->name ?? '-' }}</td>
                <td>{{ $c->transaction?->product?->name ?? '-' }}</td>
                <td class="text-right font-semibold">Rp {{ number_format($c->amount, 0, ',', '.') }}</td>
                <td class="text-right">
                    <form method="POST" action="{{ route('admin.commissions.payout', $c) }}">
                        @csrf
                        <button class="btn-secondary btn-sm">Cairkan</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty">Tidak ada komisi pending.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
