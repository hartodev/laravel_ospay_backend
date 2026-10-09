{{-- Langkah alur beli, mengikuti urutan layar mobile. $step = 1..4 --}}
@php $steps = ['Layanan', 'Produk', 'Bayar', 'Selesai']; @endphp
<ol class="mb-5 flex items-center gap-2 text-xs font-semibold text-slate-400" aria-label="Langkah pembelian">
    @foreach ($steps as $i => $s)
        @php $n = $i + 1; $on = $n === $step; $done = $n < $step; @endphp
        <li class="flex items-center gap-2 {{ $on ? 'text-brand-700' : ($done ? 'text-slate-600' : '') }}" @if ($on) aria-current="step" @endif>
            <span class="grid size-6 place-items-center rounded-full text-[11px] {{ $on ? 'bg-brand-500 text-white' : ($done ? 'bg-brand-100 text-brand-700' : 'bg-slate-200 text-slate-500') }}">
                @if ($done) @include('partials.icon', ['name' => 'check', 'class' => 'size-3.5', 'sw' => 3]) @else {{ $n }} @endif
            </span>
            <span class="{{ $on ? '' : 'hidden sm:inline' }}">{{ $s }}</span>
        </li>
        @if (! $loop->last)<li class="h-px w-4 bg-slate-300 sm:w-8" aria-hidden="true"></li>@endif
    @endforeach
</ol>
