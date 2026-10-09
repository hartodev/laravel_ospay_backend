@extends('layouts.app')
@section('title', 'Deposit')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    @include('partials.segmented', ['items' => [
        ['Transaksi', route('transactions.index'), false],
        ['Topup', route('agen.deposits.index'), true],
    ]])
    <a href="{{ route('agen.deposits.create') }}" class="btn-primary">Top up saldo</a>
</div>

<div class="grid gap-3 xl:grid-cols-2">
    @forelse ($deposits as $d)
        <a href="{{ route('agen.deposits.show', $d) }}" class="card group flex items-center gap-4 p-4 transition hover:border-brand-300 hover:shadow-lg hover:shadow-brand-500/10">
            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-600">@include('partials.icon', ['name' => 'download', 'class' => 'size-6'])</span>
            <span class="min-w-0 flex-1">
                <span class="block font-semibold text-slate-900">Top up saldo</span>
                <span class="block truncate font-mono text-xs text-slate-500">{{ $d->transaction_number }}</span>
                <span class="block text-xs text-slate-400">{{ $d->created_at->format('d M Y H:i') }}</span>
            </span>
            <span class="flex shrink-0 flex-col items-end gap-1.5">
                <span class="font-bold text-slate-900">Rp {{ number_format($d->amount, 0, ',', '.') }}</span>
                @include('partials.badge', ['status' => $d->status])
            </span>
        </a>
    @empty
        <div class="card p-12 text-center text-slate-500 xl:col-span-2">Belum ada deposit.</div>
    @endforelse
</div>
<div class="mt-6">{{ $deposits->links() }}</div>
@endsection
