@extends('layouts.app')
@section('title', 'Top Up Saldo')

@section('content')
@php $nominals = [50000, 100000, 250000, 500000, 1000000]; @endphp
<form method="POST" action="{{ route('agen.deposits.store') }}" class="grid max-w-4xl gap-6 lg:grid-cols-5">
    @csrf

    {{-- Kartu pratinjau nominal, angkanya ikut berubah seperti di mobile --}}
    <section class="relative overflow-hidden rounded-3xl bg-brand-500 p-6 text-white shadow-lg shadow-brand-500/25 sm:p-8 lg:col-span-2 lg:self-start">
        <div class="pointer-events-none absolute -right-12 -top-12 size-44 rounded-full bg-white/10"></div>
        <div class="relative text-sm text-blue-100">Nominal topup</div>
        <div id="preview" class="relative mt-1 text-3xl font-extrabold tracking-tight">Rp 0</div>
        <div class="relative mt-4 flex items-center gap-2 text-xs text-blue-100">
            @include('partials.icon', ['name' => 'card', 'class' => 'size-4']) Bayar via QRIS, bisa dari app bank atau e-wallet apa pun.
        </div>
    </section>

    <section class="card card-pad space-y-5 lg:col-span-3">
        <div>
            <h2 class="mb-3 font-bold tracking-tight text-slate-900">Pilih nominal</h2>
            <div class="grid grid-cols-2 gap-3">
                @foreach ($nominals as $n)
                    <button type="button" data-nominal="{{ $n }}"
                            class="nominal rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-sm font-semibold text-slate-700 transition hover:border-brand-300">
                        Rp {{ number_format($n, 0, ',', '.') }}
                    </button>
                @endforeach
            </div>
        </div>

        @include('partials.input', ['name' => 'amount', 'label' => 'atau masukkan nominal lain (min. Rp 10.000)', 'type' => 'number', 'min' => 10000, 'required' => true])

        <p class="text-xs text-slate-500">Saldo bertambah otomatis setelah pembayaran diterima.</p>
        <button class="btn-primary w-full py-3 text-base">Buat kode bayar</button>
    </section>
</form>

@push('scripts')
<script>
    (() => {
        const input = document.getElementById('amount'), preview = document.getElementById('preview');
        const tiles = document.querySelectorAll('[data-nominal]');
        const fmt = (n) => 'Rp ' + (n || 0).toLocaleString('id-ID');
        const sync = () => {
            const v = parseInt(input.value, 10) || 0;
            preview.textContent = fmt(v);
            tiles.forEach((t) => {
                const on = +t.dataset.nominal === v;
                t.classList.toggle('!border-brand-500', on);
                t.classList.toggle('!bg-brand-50', on);
                t.classList.toggle('!text-brand-700', on);
            });
        };
        tiles.forEach((t) => t.addEventListener('click', () => { input.value = t.dataset.nominal; sync(); }));
        input.addEventListener('input', sync);
        sync();
    })();
</script>
@endpush
@endsection
