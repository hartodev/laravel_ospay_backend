@php
    // [label, nilai kolom products.type, ikon]. Sesuaikan 'type' dengan isi tabel products Anda.
    $services = [
        ['Pulsa', 'pulsa', 'phone'], ['Data', 'data', 'wifi'], ['PLN', 'pln', 'bolt'], ['E-toll', 'etoll', 'card'],
        ['Game', 'game', 'game'], ['Voucher', 'voucher', 'ticket'], ['Streaming', 'streaming', 'play'], ['Lainnya', null, 'dots'],
    ];
@endphp
<h2 class="mb-4 text-lg font-bold tracking-tight text-slate-900">Layanan</h2>
<div class="grid grid-cols-4 gap-x-3 gap-y-6 sm:max-w-xl">
    @foreach ($services as [$label, $type, $icon])
        <a href="{{ route('products.index', $type ? ['type' => $type] : []) }}" class="group flex flex-col items-center gap-2 text-center text-sm text-slate-600">
            <span class="tile size-16">@include('partials.icon', ['name' => $icon, 'class' => 'size-6'])</span>
            {{ $label }}
        </a>
    @endforeach
</div>
