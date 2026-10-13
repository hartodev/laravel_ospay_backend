@extends('layouts.split')
@section('title', match ($reason) {
    'pending' => 'Toko belum aktif',
    'suspended' => 'Toko tidak aktif',
    default => 'Toko tidak ditemukan',
})

@section('eyebrow', 'Ups')
@section('headline', 'Toko ini belum bisa dibuka.')
@section('lead', 'Periksa kembali alamat toko, atau cari toko lain lewat halaman utama.')

@section('back')
    <a href="{{ \App\Support\Tenancy::url(null, '/login') }}" class="hover:text-brand-700">Cari toko lain</a>
@endsection

@section('subtitle')
    @if ($reason === 'pending')
        Toko <strong>{{ \App\Support\Tenancy::hostFor($subdomain) }}</strong> masih menunggu persetujuan superadmin.
    @elseif ($reason === 'suspended')
        Toko <strong>{{ \App\Support\Tenancy::hostFor($subdomain) }}</strong> sedang dinonaktifkan. Hubungi superadmin untuk informasi lebih lanjut.
    @else
        Toko dengan alamat <strong>{{ \App\Support\Tenancy::hostFor($subdomain) }}</strong> tidak ditemukan.
    @endif
@endsection

@section('content')
<a href="{{ \App\Support\Tenancy::url(null, '/login') }}" class="btn-primary h-12 w-full">Cari toko Anda</a>
@endsection

@section('footer')
    Mau punya toko sendiri? <a class="link font-bold" href="{{ \App\Support\Tenancy::url(null, '/daftar') }}">Daftar toko</a>
@endsection
