@extends('layouts.split')
@section('title', 'Daftar toko baru')

@section('eyebrow', 'Mulai jadi agen')
@section('headline', 'Satu toko khusus untuk bisnis Anda.')
@section('lead', 'Daftarkan toko, dapatkan alamat toko sendiri, lalu pelanggan Anda login dan bertransaksi lewat alamat toko Anda.')

@section('points')
    <ul class="mt-10 space-y-3 text-xs text-blue-100/80">
        @foreach (['Alamat khusus toko Anda', 'Aktif setelah disetujui superadmin', 'Pelanggan yang daftar otomatis jadi sub-user Anda'] as $point)
            <li class="flex items-center gap-2.5">
                <svg class="size-4 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg>
                {{ $point }}
            </li>
        @endforeach
    </ul>
@endsection

@section('back')
    <a href="{{ route('login') }}" class="hover:text-brand-700">Kembali ke pencarian toko</a>
@endsection

@section('subtitle', 'Isi data toko dan akun agen Anda di bawah ini.')

@section('content')
<form method="POST" action="{{ route('register-agen.store') }}" class="space-y-4">
    @csrf
    @include('partials.input', ['name' => 'store_name', 'label' => 'Nama toko', 'required' => true, 'placeholder' => 'Toko Berkah Pulsa'])

    <div>
        <label for="subdomain" class="label">Kode toko</label>
        @php $pathMode = \App\Support\Tenancy::isPathMode(); @endphp
        <div class="flex items-center overflow-hidden rounded-2xl border bg-white transition focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/15 @error('subdomain') border-red-400 @else border-slate-200 @enderror">
            @if ($pathMode)
                <span class="whitespace-nowrap bg-slate-50 px-3 py-3.5 text-xs font-semibold text-slate-500">{{ config('tenancy.main_domain') }}/</span>
            @endif
            <input id="subdomain" name="subdomain" type="text" required autocomplete="off"
                   autocapitalize="none" spellcheck="false" placeholder="tokoberkah"
                   value="{{ old('subdomain') }}"
                   class="h-12 w-full border-0 bg-transparent px-4 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-0">
            @unless ($pathMode)
                <span class="whitespace-nowrap bg-slate-50 px-3 py-3.5 text-xs font-semibold text-slate-500">.{{ config('tenancy.main_domain') }}</span>
            @endunless
        </div>
        @error('subdomain')
            <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
        @else
            <p class="mt-1.5 text-xs text-slate-400">3-30 karakter: huruf kecil, angka, dan tanda minus. Ini jadi alamat toko Anda.</p>
        @enderror
    </div>

    <div class="flex items-center gap-3 pt-2 text-[11px] font-bold uppercase tracking-widest text-slate-400">
        <span class="h-px flex-1 bg-slate-200"></span> Akun agen Anda <span class="h-px flex-1 bg-slate-200"></span>
    </div>

    @include('partials.input', ['name' => 'name', 'label' => 'Nama lengkap', 'required' => true])
    @include('partials.input', ['name' => 'email', 'label' => 'Email login', 'type' => 'email', 'required' => true, 'placeholder' => 'nama@email.com'])
    @include('partials.input', ['name' => 'phone', 'label' => 'No. HP', 'required' => true, 'placeholder' => '08xxxxxxxxxx'])
    <div class="grid gap-4 sm:grid-cols-2">
        @include('partials.input', ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true, 'placeholder' => 'Min. 8 karakter'])
        @include('partials.input', ['name' => 'password_confirmation', 'label' => 'Konfirmasi', 'type' => 'password', 'required' => true])
    </div>

    <button class="btn-primary h-12 w-full">Daftarkan toko</button>
</form>
@endsection

@section('footer')
    Sudah punya toko? <a class="link font-bold" href="{{ route('login') }}">Cari toko Anda</a>
@endsection

@push('scripts')
<script>
    (function () {
        var store = document.getElementById('store_name');
        var sub = document.getElementById('subdomain');
        if (!store || !sub) return;

        var touched = sub.value !== '';
        var clean = function (v) {
            return v.toLowerCase().replace(/[^a-z0-9-]+/g, '-').replace(/-{2,}/g, '-').replace(/^-+/, '').slice(0, 30);
        };

        // Kode toko terisi otomatis dari nama toko, sampai pengguna mengetiknya sendiri.
        store.addEventListener('input', function () {
            if (!touched) sub.value = clean(store.value).replace(/-+$/, '');
        });
        sub.addEventListener('input', function () {
            touched = true;
            sub.value = clean(sub.value);
        });
    })();
</script>
@endpush
