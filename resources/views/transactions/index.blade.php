@extends('layouts.app')
@section('title', 'Transaksi')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    @if (auth()->user()->isAgent())
        @include('partials.segmented', ['items' => [
            ['Transaksi', route('transactions.index'), true],
            ['Topup', route('agen.deposits.index'), false],
        ]])
    @else
        <div></div>
    @endif

    <form method="GET" class="flex gap-2 text-sm">
        <select name="status" class="field">
            <option value="">Semua status</option>
            @foreach (['pending', 'processing', 'success', 'failed'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
            @endforeach
        </select>
        <button class="btn-primary">Filter</button>
    </form>
</div>

@include('transactions._table')

<div class="mt-6">{{ $transactions->links() }}</div>
@endsection
