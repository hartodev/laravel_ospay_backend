@extends('layouts.app')
@section('title', 'Profil')

@section('content')
<div class="grid md:grid-cols-2 gap-6 max-w-4xl">
    <form method="POST" action="{{ route('profile.update') }}" class="card p-6 space-y-4">
        @csrf @method('PUT')
        <h2 class="font-semibold">Data profil</h2>
        <div class="text-sm text-slate-500">{{ $user->email }} &middot; {{ $user->role }}</div>
        @include('partials.input', ['name' => 'name', 'label' => 'Nama', 'value' => $user->name, 'required' => true])
        @include('partials.input', ['name' => 'phone', 'label' => 'No. HP', 'value' => $user->phone])
        <button class="btn-primary">Simpan</button>
    </form>

    <form method="POST" action="{{ route('profile.password') }}" class="card p-6 space-y-4">
        @csrf @method('PUT')
        <h2 class="font-semibold">Ganti password</h2>
        @include('partials.input', ['name' => 'current_password', 'label' => 'Password saat ini', 'type' => 'password', 'required' => true])
        @include('partials.input', ['name' => 'password', 'label' => 'Password baru', 'type' => 'password', 'required' => true])
        @include('partials.input', ['name' => 'password_confirmation', 'label' => 'Ulangi password baru', 'type' => 'password', 'required' => true])
        <button class="btn-primary">Ganti password</button>
    </form>
</div>
@endsection
