@extends('layouts.split')
@section('title', 'Daftar')

@section('eyebrow', 'Mulai sekarang')
@section('headline', 'Beli pulsa, token, dan tagihan dengan mudah.')
@section('lead', 'Buat akun gratis, lalu bayar per transaksi lewat QRIS.')

@section('back')
    <a href="{{ route('login') }}" class="hover:text-brand-700">Kembali ke login</a>
@endsection

@section('subtitle', 'Isi data diri Anda di bawah ini.')

@section('content')
<form method="POST" action="{{ route('register.store') }}" class="space-y-4">
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
    Sudah punya akun? <a class="link font-bold" href="{{ route('login') }}">Masuk</a>
@endsection
