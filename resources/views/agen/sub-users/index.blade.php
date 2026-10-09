@extends('layouts.app')
@section('title', 'Sub-user')

@section('content')
<a href="{{ route('agen.sub-users.create') }}" class="btn-primary mb-4">Tambah sub-user</a>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr><th>Nama</th><th>Email</th><th>No. HP</th><th>Status</th><th>Terdaftar</th></tr>
        </thead>
        <tbody>
        @forelse ($subUsers as $s)
            <tr>
                <td>{{ $s->name }}</td>
                <td>{{ $s->email }}</td>
                <td>{{ $s->phone ?: '-' }}</td>
                <td>@include('partials.badge', ['status' => $s->status])</td>
                <td>{{ $s->created_at->format('d M Y') }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty">Belum ada sub-user.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $subUsers->links() }}</div>
@endsection
