@extends('layouts.app')
@section('title', 'Tambah Sub-user')

@section('content')
<form method="POST" action="{{ route('agen.sub-users.store') }}" class="max-w-md card p-6 space-y-4">
    @csrf
    @include('partials.input', ['name' => 'name', 'label' => 'Nama', 'required' => true])
    @include('partials.input', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true])
    @include('partials.input', ['name' => 'phone', 'label' => 'No. HP (opsional)'])
    @include('partials.input', ['name' => 'password', 'label' => 'Password (min. 8 karakter)', 'type' => 'password', 'required' => true])
    <div class="flex gap-3">
        <button class="btn-primary">Simpan</button>
        <a href="{{ route('agen.sub-users.index') }}" class="btn-secondary">Batal</a>
    </div>
</form>
@endsection
