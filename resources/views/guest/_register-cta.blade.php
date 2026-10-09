{{-- Ajakan jadi pengguna aplikasi, bukan hanya membeli dari landing. --}}
<div class="card overflow-hidden">
    <div class="bg-ink-900 px-5 py-4 text-white">
        <div class="text-sm font-semibold text-brand-200">Sering beli?</div>
        <div class="mt-0.5 text-lg font-bold tracking-tight">Jadi pengguna {{ config('app.name') }}</div>
    </div>
    <div class="space-y-3 p-5 text-sm">
        <ul class="space-y-2 text-slate-600">
            <li class="flex gap-2"><span class="text-emerald-600">✓</span> Riwayat transaksi tersimpan, tanpa perlu menyimpan kode</li>
            <li class="flex gap-2"><span class="text-emerald-600">✓</span> Beli lebih cepat dari web maupun aplikasi</li>
            <li class="flex gap-2"><span class="text-emerald-600">✓</span> Punya konter? Daftar sebagai agen dan dapat harga khusus</li>
        </ul>
        <a href="{{ route('register') }}" class="btn-primary w-full">Daftar gratis</a>
        <div class="flex items-center justify-between text-xs">
            <a href="{{ route('login') }}" class="link">Sudah punya akun? Masuk</a>
            <a href="{{ route('register-agen') }}" class="link">Daftar agen</a>
        </div>
    </div>
</div>
