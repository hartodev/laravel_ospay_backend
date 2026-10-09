@php
    $s = strtolower((string) $status);
    [$tone, $dot] = match ($s) {
        'success', 'paid', 'active', 'refunded', 'credit' => ['bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'bg-emerald-500'],
        'pending', 'processing' => ['bg-amber-50 text-amber-800 ring-amber-600/20', 'bg-amber-500'],
        'failed', 'expired', 'cancelled', 'suspended', 'debit' => ['bg-red-50 text-red-700 ring-red-600/20', 'bg-red-500'],
        default => ['bg-slate-100 text-slate-600 ring-slate-500/20', 'bg-slate-400'],
    };
@endphp
<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize ring-1 ring-inset {{ $tone }}"><span class="size-1.5 rounded-full {{ $dot }}"></span>{{ $status ?: '-' }}</span>
