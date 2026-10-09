@extends('layouts.split')
@section('title', 'Login superadmin')

@section('eyebrow', 'Area pengelola')
@section('headline', 'Kelola agen, produk, dan komisi.')
@section('lead', 'Halaman ini khusus superadmin. Agen dan pelanggan masuk lewat halaman tokonya masing-masing.')

@section('back')
    <a href="{{ route('login') }}" class="hover:text-brand-700">Kembali ke pencarian toko</a>
@endsection

@section('subtitle', 'Gunakan email dan password superadmin.')

@section('content')
<form method="POST" action="{{ route('superadmin.login.store') }}" class="space-y-4">
    @csrf
    @include('partials.input', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true])
    @include('partials.input', ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true])
    <label class="flex items-center gap-2 text-sm text-slate-600">
        <input type="checkbox" name="remember" value="1" class="rounded border-slate-300"> Ingat saya
    </label>
    <button class="btn-primary h-12 w-full">Masuk ke dashboard</button>
</form>
@endsection
