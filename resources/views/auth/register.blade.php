@extends('layouts.guest')
@section('title', 'Daftar')

@section('content')
<form method="POST" action="{{ route('register.store') }}" class="space-y-4">
    @csrf
    @include('partials.input', ['name' => 'name', 'label' => 'Nama', 'required' => true])
    @include('partials.input', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true])
    @include('partials.input', ['name' => 'phone', 'label' => 'No. HP (opsional)'])
    @include('partials.input', ['name' => 'password', 'label' => 'Password (min. 8 karakter)', 'type' => 'password', 'required' => true])
    @include('partials.input', ['name' => 'password_confirmation', 'label' => 'Ulangi password', 'type' => 'password', 'required' => true])
    <button class="btn-primary w-full">Daftar</button>
</form>
@endsection

@section('footer')
    Sudah punya akun? <a class="link" href="{{ route('login') }}">Masuk</a>
@endsection
