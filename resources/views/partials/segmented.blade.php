{{-- Kontrol segmen ala mobile. $items = [[label, url, aktif?], ...] --}}
<div class="inline-flex rounded-2xl bg-slate-200/70 p-1 text-sm font-semibold">
    @foreach ($items as [$label, $url, $on])
        <a href="{{ $url }}" @if ($on) aria-current="page" @endif
           class="rounded-xl px-5 py-2 transition {{ $on ? 'bg-white text-brand-600 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">{{ $label }}</a>
    @endforeach
</div>
