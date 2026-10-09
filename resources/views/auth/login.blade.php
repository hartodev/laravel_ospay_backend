@extends('layouts.guest')
@section('title', 'Masuk')

@section('content')
<form method="POST" action="{{ route('login.store') }}" class="space-y-4">
    @csrf
    @include('partials.input', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true])
    @include('partials.input', ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true])
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="remember" value="1"> Ingat saya
    </label>
    <button class="btn-primary w-full">Masuk</button>
</form>
@endsection

@section('footer')
    Belum punya akun? <a class="link" href="{{ route('register') }}">Daftar</a>
    &middot; <a class="link" href="{{ route('register-agen') }}">Daftar sebagai agen</a>
@endsection
