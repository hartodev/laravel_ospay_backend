@extends('layouts.app')
@section('title', 'Manajemen Agen')

@section('content')
<div class="flex flex-wrap gap-2 mb-5 text-sm">
    @foreach ([['', 'Semua'], ['pending', 'Pending'], ['active', 'Aktif'], ['suspended', 'Suspended']] as [$key, $label])
        <a href="{{ route('admin.agents.index', $key ? ['status' => $key] : []) }}"
           class="rounded-full px-4 py-1.5 font-semibold border transition-colors {{ (string) $status === $key ? 'bg-brand-700 text-white border-brand-700' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50' }}">
            {{ $label }}@if ($key === 'pending' && $pendingCount) ({{ $pendingCount }})@endif
        </a>
    @endforeach
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr><th>Nama</th><th>Toko</th><th>Kontak</th><th>Status</th><th class="text-right">Saldo</th><th>Daftar</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        @forelse ($agents as $a)
            <tr>
                <td class="font-semibold text-slate-900">{{ $a->name }}</td>
                <td class="whitespace-nowrap">
                    @if ($a->subdomain)
                        <span class="font-medium text-slate-700">{{ $a->subdomain }}</span><div class="text-xs text-slate-400">{{ $a->storeHost() }}</div>
                    @else - @endif
                </td>
                <td>{{ $a->email }}<div class="text-xs text-slate-400">{{ $a->phone }}</div></td>
                <td>@include('partials.badge', ['status' => $a->status])</td>
                <td class="text-right">{{ $a->wallet ? 'Rp ' . number_format($a->wallet->balance, 0, ',', '.') : '-' }}</td>
                <td class="whitespace-nowrap">{{ $a->created_at->format('d M Y') }}</td>
                <td>
                    <div class="flex gap-2">
                        @if ($a->isPending())
                            @include('admin.agents._action', ['agent' => $a, 'action' => 'approve', 'label' => 'Setujui', 'class' => 'btn-primary btn-sm'])
                            @include('admin.agents._action', ['agent' => $a, 'action' => 'reject', 'label' => 'Tolak', 'class' => 'btn-danger btn-sm', 'confirm' => 'Tolak pendaftaran agen ini?'])
                        @elseif ($a->isActive())
                            @include('admin.agents._action', ['agent' => $a, 'action' => 'suspend', 'label' => 'Nonaktifkan', 'class' => 'btn-danger btn-sm', 'confirm' => 'Nonaktifkan agen ini?'])
                        @else
                            @include('admin.agents._action', ['agent' => $a, 'action' => 'activate', 'label' => 'Aktifkan', 'class' => 'btn-primary btn-sm'])
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="empty">Tidak ada agen.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $agents->links() }}</div>
@endsection
