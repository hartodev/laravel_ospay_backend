@extends('layouts.split')
@section('title', 'Masuk')

@section('eyebrow', 'Selamat datang kembali')
@section('headline', 'Belanja dan jualan PPOB, dimulai dari toko Anda.')
@section('lead', 'Masuk dengan email dan password akun Anda. Anda akan langsung diarahkan ke dashboard.')

@section('back')
    <a href="{{ route('home') }}" class="hover:text-brand-700">Kembali ke beranda</a>
@endsection

@section('subtitle', 'Gunakan email dan password akun Anda.')

@section('content')
<form method="POST" action="{{ route('login.store') }}" class="space-y-4">
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

{{-- Jalur opsional: masuk lewat halaman toko (isi subdomain dulu). --}}
<details class="mt-6 rounded-2xl border border-slate-200 bg-slate-50/60 p-4" @if(!empty($subdomain) || $errors->has('subdomain')) open @endif>
    <summary class="cursor-pointer text-sm font-semibold text-slate-600">Punya link toko? Masuk lewat halaman toko</summary>

    <form method="POST" action="{{ route('login.go') }}" class="mt-4 space-y-3">
        @csrf
        <div>
            <label for="subdomain" class="label">Subdomain toko</label>
            <div class="flex items-center overflow-hidden rounded-2xl border bg-white transition focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/15 @error('subdomain') border-red-400 @else border-slate-200 @enderror">
                <input id="subdomain" name="subdomain" type="text" required autocomplete="off"
                       autocapitalize="none" spellcheck="false" placeholder="contoh: toko-abc"
                       value="{{ old('subdomain', $subdomain ?? '') }}"
                       class="h-12 w-full border-0 bg-transparent px-4 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-0">
                <span class="whitespace-nowrap bg-slate-50 px-3 py-3.5 text-xs font-semibold text-slate-500">.{{ config('tenancy.main_domain') }}</span>
            </div>
            @error('subdomain')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>

        <button class="btn h-11 w-full border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">
            Lanjut ke halaman toko
        </button>
    </form>
</details>
@endsection

@section('footer')
    Belum punya akun? <a class="link font-bold" href="{{ route('register') }}">Daftar</a>
    <span class="mx-2 text-slate-300">|</span>
    Ingin jadi agen? <a class="link font-bold" href="{{ route('register-agen') }}">Daftar toko</a>
@endsection
