{{-- Banner info seperti carousel di Beranda mobile. Mobile: geser samping; desktop: 3 kolom. $isAgen = bool --}}
@php
    $banners = [
        ['Bayar pakai QRIS', 'Bisa dari aplikasi bank atau e-wallet apa saja.', 'card', 'from-brand-600 to-brand-500'],
        $isAgen
            ? ['Topup saldo mudah', 'Isi saldo via QRIS, bertambah otomatis.', 'download', 'from-indigo-600 to-indigo-500']
            : ['Tanpa saldo, tanpa ribet', 'Bayar langsung per transaksi.', 'wallet', 'from-indigo-600 to-indigo-500'],
        ['Pulsa & Token PLN', 'Proses cepat, status bisa dipantau di Riwayat.', 'bolt', 'from-teal-600 to-teal-500'],
    ];
@endphp
<div class="-mx-4 flex snap-x snap-mandatory gap-3 overflow-x-auto px-4 pb-1 sm:mx-0 sm:grid sm:grid-cols-3 sm:overflow-visible sm:px-0">
    @foreach ($banners as [$title, $text, $icon, $grad])
        <div class="relative w-[78%] shrink-0 snap-start overflow-hidden rounded-3xl bg-gradient-to-br {{ $grad }} p-5 text-white shadow-card sm:w-auto">
            <div class="pointer-events-none absolute -right-8 -top-8 size-28 rounded-full bg-white/10"></div>
            <span class="relative grid size-10 place-items-center rounded-xl bg-white/20">@include('partials.icon', ['name' => $icon, 'class' => 'size-5'])</span>
            <div class="relative mt-3 font-bold">{{ $title }}</div>
            <div class="relative mt-0.5 text-sm text-white/80">{{ $text }}</div>
        </div>
    @endforeach
</div>
