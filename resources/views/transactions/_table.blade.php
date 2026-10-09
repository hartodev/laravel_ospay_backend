{{-- Daftar transaksi berbentuk kartu baris seperti layar Riwayat di mobile. $cols = kelas grid kolom. --}}
<div class="grid gap-3 {{ $cols ?? 'xl:grid-cols-2' }}">
    @forelse ($transactions as $t)
        @php
            $tone = match (strtolower((string) $t->status)) {
                'success' => 'bg-emerald-50 text-emerald-600',
                'pending', 'processing' => 'bg-amber-50 text-amber-600',
                'failed', 'expired', 'cancelled' => 'bg-red-50 text-red-500',
                default => 'bg-slate-100 text-slate-500',
            };
        @endphp
        <a href="{{ route('transactions.show', $t) }}" class="card group flex items-center gap-4 p-4 transition hover:border-brand-300 hover:shadow-lg hover:shadow-brand-500/10">
            <span class="grid size-14 shrink-0 place-items-center rounded-2xl {{ $tone }}">@include('partials.icon', ['for' => $t->product?->type . ' ' . $t->product?->name, 'class' => 'size-6'])</span>
            <span class="min-w-0 flex-1">
                <span class="block truncate font-semibold text-slate-900">{{ $t->product?->name }}</span>
                <span class="block truncate text-sm text-slate-500">{{ $t->customer_id }}</span>
                <span class="block truncate text-xs text-slate-400">{{ $t->created_at->format('d M Y H:i') }} &middot; {{ $t->payment_method === 'wallet' ? 'Wallet' : 'QRIS' }}</span>
            </span>
            <span class="flex shrink-0 flex-col items-end gap-1.5">
                <span class="font-bold text-slate-900">Rp {{ number_format($t->sell_price, 0, ',', '.') }}</span>
                @include('partials.badge', ['status' => $t->status])
            </span>
        </a>
    @empty
        <div class="card p-12 text-center text-slate-500 {{ isset($cols) && $cols === '' ? '' : 'xl:col-span-2' }}">Belum ada transaksi.</div>
    @endforelse
</div>
