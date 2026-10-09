@extends('layouts.split')
@section('title', 'Masuk ke ' . $tenant->storeLabel())

@section('eyebrow', 'Selamat datang kembali')
@section('headline', 'Masuk ke ' . $tenant->storeLabel() . '.')
@section('lead', 'Gunakan akun yang terdaftar di toko ini untuk melanjutkan.')

@section('back')
    <a href="{{ route('login') }}" class="hover:text-brand-700">Bukan toko ini? Cari toko lain</a>
@endsection

@section('subtitle', 'Gunakan email dan password akun toko ini.')

@section('content')
<form method="POST" action="{{ route('tenant.login.store') }}" class="space-y-4">
    @csrf
    @include('partials.input', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'placeholder' => 'nama@email.com'])
    @include('partials.input', ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true, 'placeholder' => 'Masukkan password'])
    <label class="flex items-center gap-2 text-sm text-slate-600">
        <input type="checkbox" name="remember" value="1" class="rounded border-slate-300"> Ingat saya
    </label>
    <button class="btn-primary h-12 w-full">
        Masuk ke dashboard
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </button>
</form>
@endsection

@section('footer')
    Belum punya akun di toko ini? <a class="link font-bold" href="{{ route('tenant.register') }}">Daftar sebagai pelanggan</a>
@endsection
