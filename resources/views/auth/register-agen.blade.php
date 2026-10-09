@extends('layouts.guest')
@section('title', 'Daftar Agen')

@section('content')
<p class="text-sm text-slate-500 mb-4">Akun agen akan aktif setelah disetujui superadmin.</p>
<form method="POST" action="{{ route('register-agen.store') }}" class="space-y-4">
    @csrf
    @include('partials.input', ['name' => 'name', 'label' => 'Nama / nama usaha', 'required' => true])
    @include('partials.input', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true])
    @include('partials.input', ['name' => 'phone', 'label' => 'No. HP', 'required' => true])
    @include('partials.input', ['name' => 'password', 'label' => 'Password (min. 8 karakter)', 'type' => 'password', 'required' => true])
    @include('partials.input', ['name' => 'password_confirmation', 'label' => 'Ulangi password', 'type' => 'password', 'required' => true])
    <button class="btn-primary w-full">Kirim Pendaftaran</button>
</form>
@endsection

@section('footer')
    <a class="link" href="{{ route('login') }}">Kembali ke login</a>
@endsection
