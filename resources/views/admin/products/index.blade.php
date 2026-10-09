@extends('layouts.app')
@section('title', 'Produk & Harga')

@section('content')
<div class="grid md:grid-cols-2 gap-4 mb-6">
    <form method="POST" action="{{ route('admin.products.sync') }}" class="card p-4 text-sm"
          onsubmit="this.querySelector('button').disabled = true">
        @csrf
        <div class="font-semibold mb-1">Sync dari IAK</div>
        <p class="text-slate-500 mb-3">Ambil price list terbaru lewat API. Hanya base_price &amp; status yang diupdate; harga jual tidak diubah.</p>
        <button class="btn-primary">Sync sekarang</button>
    </form>

    <form method="POST" action="{{ route('admin.products.import') }}" enctype="multipart/form-data"
          class="card p-4 text-sm" onsubmit="this.querySelector('button').disabled = true">
        @csrf
        <div class="font-semibold mb-1">Import dari file pricelist (.xlsx)</div>
        <p class="text-slate-500 mb-3">Aturannya sama dengan sync. Sheet pascabayar dilewati.</p>
        <div class="flex gap-2">
            <input type="file" name="file" accept=".xlsx" required class="flex-1 text-sm">
            <button class="btn-secondary">Import</button>
        </div>
        @error('file')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </form>
</div>

<form method="GET" class="card p-4 mb-4 grid sm:grid-cols-5 gap-3 text-sm items-center">
    <input name="search" value="{{ request('search') }}" placeholder="Cari nama / kode / operator" class="field sm:col-span-2">
    <select name="status" class="field">
        <option value="">Semua status</option>
        <option value="active" @selected(request('status') === 'active')>active</option>
        <option value="inactive" @selected(request('status') === 'inactive')>inactive</option>
    </select>
    <label class="flex items-center gap-2"><input type="checkbox" name="needs_pricing" value="1" @checked(request()->boolean('needs_pricing'))> Belum ada margin</label>
    <button class="btn-primary">Filter</button>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Produk</th>
                <th class="text-right">Harga beli</th>
                <th>Harga user</th>
                <th>Harga agen</th>
                <th>Harga tamu</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse ($products as $p)
            @php $needs = $p->price_user === $p->base_price && $p->price_agent === $p->base_price; @endphp
            <tr class="{{ $needs ? 'bg-amber-50' : '' }}">
                <td>
                    <div class="font-medium">{{ $p->name }}</div>
                    <div class="text-xs text-slate-400">{{ $p->product_code }} &middot; {{ $p->type }} &middot; {{ $p->brand }}</div>
                </td>
                <td class="text-right whitespace-nowrap">Rp {{ number_format($p->base_price, 0, ',', '.') }}</td>
                <td><input form="f{{ $p->id }}" type="number" min="0" name="price_user" value="{{ $p->price_user }}" class="field field-sm w-28"></td>
                <td><input form="f{{ $p->id }}" type="number" min="0" name="price_agent" value="{{ $p->price_agent }}" class="field field-sm w-28"></td>
                <td><input form="f{{ $p->id }}" type="number" min="0" name="price_guest" value="{{ $p->price_guest }}" placeholder="kosong = tidak dijual" class="field field-sm w-32"></td>
                <td>
                    <select form="f{{ $p->id }}" name="status" class="field field-sm">
                        <option value="active" @selected($p->status === 'active')>active</option>
                        <option value="inactive" @selected($p->status === 'inactive')>inactive</option>
                    </select>
                </td>
                <td>
                    <form id="f{{ $p->id }}" method="POST" action="{{ route('admin.products.update', $p) }}">
                        @csrf @method('PATCH')
                        <button class="btn-primary btn-sm">Simpan</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="empty">Belum ada produk. Jalankan sync atau import pricelist.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $products->links() }}</div>
@endsection
