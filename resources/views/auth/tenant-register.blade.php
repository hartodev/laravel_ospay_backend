@extends('layouts.split')
@section('title', 'Daftar di ' . $tenant->storeLabel())

@section('eyebrow', 'Mulai sekarang')
@section('headline', 'Buat akun di ' . $tenant->storeLabel() . '.')
@section('lead', 'Akun Anda otomatis terdaftar sebagai pelanggan toko ini.')

@section('back')
    <a href="{{ route('tenant.login') }}" class="hover:text-brand-700">Kembali ke login</a>
@endsection

@section('subtitle', 'Isi data diri Anda di bawah ini.')

@section('content')
<form method="POST" action="{{ route('tenant.register.store') }}" class="space-y-4">
    @csrf
    @include('partials.input', ['name' => 'name', 'label' => 'Nama lengkap', 'required' => true])
    @include('partials.input', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'placeholder' => 'nama@email.com'])
    @include('partials.input', ['name' => 'phone', 'label' => 'No. HP (opsional)', 'placeholder' => '08xxxxxxxxxx'])
    <div class="grid gap-4 sm:grid-cols-2">
        @include('partials.input', ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true, 'placeholder' => 'Min. 8 karakter'])
        @include('partials.input', ['name' => 'password_confirmation', 'label' => 'Konfirmasi', 'type' => 'password', 'required' => true])
    </div>
    <button class="btn-primary h-12 w-full">Daftar</button>
</form>
@endsection

@section('footer')
    Sudah punya akun? <a class="link font-bold" href="{{ route('tenant.login') }}">Masuk</a>
@endsection
