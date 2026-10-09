@extends('layouts.public')
@section('title', 'Lacak pesanan')

@section('content')
<div class="mx-auto max-w-xl">
    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Lacak pesanan</h1>
    <p class="mt-1 text-sm text-slate-500">Masukkan kode transaksi yang Anda terima saat memesan (diawali <b>TRX-</b>).</p>

    <form method="POST" action="{{ route('guest.lookup') }}" class="card mt-5 p-5 sm:p-6 space-y-4">
        @csrf
        @include('partials.input', ['name' => 'code', 'label' => 'Kode transaksi', 'required' => true, 'placeholder' => 'TRX-XXXXXXXXXXXXXXXXXXXX'])
        <button class="btn-primary w-full">Cek status</button>
    </form>

    <div id="recent" class="card mt-5 hidden p-5 sm:p-6">
        <div class="font-semibold text-slate-900">Pesanan terakhir di perangkat ini</div>
        <ul id="recent-list" class="mt-3 divide-y divide-slate-100 text-sm"></ul>
    </div>

    <div class="mt-6">@include('guest._register-cta')</div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        let list = [];
        try { list = JSON.parse(localStorage.getItem('ospay_guest_orders') || '[]'); } catch (e) {}
        if (!Array.isArray(list) || !list.length) return;
        const ul = document.getElementById('recent-list');
        const base = @json(url('/lacak'));
        list.slice(0, 5).forEach((o) => {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = base + '/' + encodeURIComponent(o.code);
            a.className = 'flex items-center justify-between gap-3 py-2.5 hover:text-brand-700';
            const left = document.createElement('span');
            left.className = 'min-w-0';
            const n = document.createElement('span');
            n.className = 'block truncate font-medium';
            n.textContent = o.name || o.code;
            const c = document.createElement('span');
            c.className = 'block truncate font-mono text-xs text-slate-400';
            c.textContent = o.code;
            left.append(n, c);
            a.append(left);
            li.append(a);
            ul.append(li);
        });
        document.getElementById('recent').classList.remove('hidden');
    })();
</script>
@endpush
