<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Isi pulsa, token PLN & paket data</title>
    <meta name="description" content="Isi pulsa, token PLN, dan paket data. Bayar QRIS, atau jadi agen dan dapat komisi.">
    @include('partials.head')
    <style>
        @keyframes floaty { 0%,100% { transform: translateY(0) } 50% { transform: translateY(-10px) } }
        @keyframes rise { from { opacity: 0; transform: translateY(18px) } to { opacity: 1; transform: none } }
        .floaty { animation: floaty 5s ease-in-out infinite }
        .rise { animation: rise .7s cubic-bezier(.2,.7,.2,1) both }
        .grid-bg { background-image: linear-gradient(rgb(255 255 255/.05) 1px, transparent 1px), linear-gradient(90deg, rgb(255 255 255/.05) 1px, transparent 1px); background-size: 44px 44px; -webkit-mask-image: radial-gradient(ellipse at 70% 30%, #000 20%, transparent 70%); mask-image: radial-gradient(ellipse at 70% 30%, #000 20%, transparent 70%) }
        @media (prefers-reduced-motion: reduce) { .floaty, .rise { animation: none } }
    </style>
</head>
<body class="bg-white">
@php $app = config('app.name'); @endphp

{{-- NAV --}}
<header class="absolute inset-x-0 top-0 z-30">
    <div class="mx-auto flex h-20 max-w-6xl items-center justify-between px-5">
        <a href="/" class="flex items-center gap-2.5 text-white">
            <img src="{{ \App\Support\AppBranding::webLogo() }}" alt="{{ config('app.name') }}" class="size-9 rounded-xl" width="40" height="40">
            <span class="text-lg font-bold tracking-tight">{{ $app }}</span>
        </a>
        <nav class="hidden items-center gap-8 text-sm font-medium text-slate-300 md:flex">
            <a href="#coba" class="hover:text-white">Coba beli</a>
            <a href="{{ route('guest.track') }}" class="hover:text-white">Lacak pesanan</a>
            <a href="#agen" class="hover:text-white">Jadi agen</a>
            <a href="#cara" class="hover:text-white">Cara kerja</a>
            <a href="#faq" class="hover:text-white">FAQ</a>
        </nav>
        <div class="flex items-center gap-2">
            @auth
                <a href="{{ route('dashboard') }}" class="btn bg-brand-500 text-white hover:bg-brand-400">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn hidden text-white hover:bg-white/10 sm:inline-flex">Masuk</a>
                <a href="{{ route('register') }}" class="btn bg-brand-500 text-white hover:bg-brand-400">Daftar</a>
            @endauth
        </div>
    </div>
</header>

{{-- HERO --}}
<section class="relative overflow-hidden bg-ink-900 pb-24 pt-32 text-white">
    <div class="grid-bg pointer-events-none absolute inset-0"></div>
    <div class="pointer-events-none absolute -right-24 top-10 size-[30rem] rounded-full bg-brand-500/25 blur-3xl"></div>
    <div class="pointer-events-none absolute -left-32 bottom-0 size-[24rem] rounded-full bg-brand-700/30 blur-3xl"></div>

    <div class="relative mx-auto grid max-w-6xl items-center gap-14 px-5 lg:grid-cols-[1.15fr_.85fr]">
        <div class="rise">
            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3.5 py-1.5 text-xs font-medium text-brand-200">
                <span class="size-2 rounded-full bg-brand-400"></span> Pulsa, PLN, data, game &amp; PPOB
            </span>
            <h1 class="mt-6 text-4xl font-extrabold leading-[1.05] tracking-tight sm:text-6xl">
                Isi ulang apa saja,<br>
                <span class="bg-gradient-to-r from-brand-300 to-brand-500 bg-clip-text text-transparent">secepat bayar QRIS.</span>
            </h1>
            <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate-300">
                Pulsa, paket data, dan token listrik dalam satu aplikasi. Mau untung dari jualan? Daftar jadi agen, isi saldo, dan terima komisi dari tiap transaksi.
            </p>
            <div class="mt-9 flex flex-wrap gap-3">
                @auth
                    <a href="{{ route('transactions.create') }}" class="btn bg-brand-500 px-7 py-3.5 text-base text-white hover:bg-brand-400">Beli sekarang</a>
                @else
                    <a href="{{ route('guest.catalog') }}" class="btn bg-brand-500 px-7 py-3.5 text-base text-white hover:bg-brand-400">Beli sekarang</a>
                    <a href="{{ route('register') }}" class="btn border border-white/25 px-7 py-3.5 text-base text-white hover:bg-white/10">Daftar gratis</a>
                @endauth
            </div>
            <ul class="mt-10 flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-400">
                <li>✓ Beli tanpa daftar</li>
                <li>✓ Bayar QRIS &amp; Virtual Account</li>
                <li>✓ Status dicek otomatis</li>
                <li>✓ Gagal? Dana dikembalikan</li>
            </ul>
        </div>

        {{-- Mockup ponsel --}}
        <div class="relative mx-auto w-full max-w-[19rem] rise" style="animation-delay:.15s">
            <div class="floaty rounded-[2.2rem] border-[7px] border-slate-700 bg-paper p-4 shadow-2xl shadow-black/40">
                <div class="mx-auto mb-4 h-1.5 w-16 rounded-full bg-slate-300"></div>
                <p class="text-xs text-slate-500">Halo, selamat datang</p>
                <p class="text-lg font-bold text-slate-900">Mau isi apa hari ini?</p>
                <div class="mt-3 grid grid-cols-3 gap-2 text-center text-[11px] font-semibold text-slate-700">
                    <div class="rounded-xl bg-white p-3 shadow-card"><div class="mx-auto mb-1.5 grid size-9 place-items-center rounded-lg bg-brand-100 text-lg">📱</div>Pulsa</div>
                    <div class="rounded-xl bg-white p-3 shadow-card"><div class="mx-auto mb-1.5 grid size-9 place-items-center rounded-lg bg-sky-100 text-lg">🌐</div>Data</div>
                    <div class="rounded-xl bg-white p-3 shadow-card"><div class="mx-auto mb-1.5 grid size-9 place-items-center rounded-lg bg-amber-100 text-lg">⚡</div>PLN</div>
                </div>
                <div class="mt-3 rounded-xl bg-white p-3 shadow-card">
                    <p class="text-[11px] text-slate-500">Transaksi terakhir</p>
                    <div class="mt-1 flex items-center justify-between text-sm"><span class="font-semibold">Pulsa 50.000</span><span class="rounded-full bg-brand-100 px-2 py-0.5 text-[11px] font-semibold text-brand-800">Sukses</span></div>
                    <div class="mt-1.5 flex items-center justify-between text-sm"><span class="font-semibold">Token PLN 100.000</span><span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800">Diproses</span></div>
                </div>
                <div class="mt-3 rounded-xl bg-brand-700 py-3 text-center text-sm font-bold text-white">Beli sekarang</div>
            </div>
            <div class="floaty absolute -left-10 top-24 rounded-xl bg-white px-3.5 py-2.5 text-xs font-bold text-slate-800 shadow-xl" style="animation-delay:1s">✅ Pulsa masuk</div>
            <div class="floaty absolute -right-6 bottom-20 rounded-xl bg-brand-500 px-3.5 py-2.5 text-xs font-bold text-white shadow-xl" style="animation-delay:2s">💰 Komisi agen masuk</div>
        </div>
    </div>
</section>

{{-- SIMULATOR --}}
<section id="coba" class="mx-auto max-w-6xl px-5 py-24">
    <div class="grid gap-12 lg:grid-cols-[.8fr_1.2fr] lg:items-center">
        <div>
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Coba rasakan alurnya.</h2>
            <p class="mt-4 leading-relaxed text-slate-600">Pilih jenis produk dan nominal. Begini kira-kira tampilan pembelian di {{ $app }}. Harga di bawah hanya contoh; harga asli tampil di halaman beli. Anda bisa membeli langsung tanpa daftar.</p>
        </div>
        <div class="card card-pad sm:p-8">
            <div id="tabs" class="grid grid-cols-3 gap-2 rounded-xl bg-slate-100 p-1.5 text-sm font-semibold">
                <button data-k="pulsa" class="rounded-lg bg-white py-2 shadow-sm">Pulsa</button>
                <button data-k="data" class="rounded-lg py-2 text-slate-500">Paket data</button>
                <button data-k="pln" class="rounded-lg py-2 text-slate-500">Token PLN</button>
            </div>
            <label class="label mt-5" id="lbl" for="no">Nomor tujuan</label>
            <input id="no" class="field" inputmode="numeric" placeholder="08xxxxxxxxxx">
            <p class="label mt-5">Pilih nominal</p>
            <div id="chips" class="grid grid-cols-2 gap-2 sm:grid-cols-4"></div>
            <div class="mt-6 flex items-center justify-between rounded-xl bg-paper p-4">
                <span class="text-sm text-slate-500">Total bayar (contoh)</span>
                <span id="total" class="text-2xl font-extrabold tracking-tight text-slate-900">Rp 0</span>
            </div>
            @auth
                <a id="go" href="{{ route('products.index') }}" class="btn-primary mt-5 w-full py-3.5 text-base">Lanjut beli</a>
            @else
                <a id="go" href="{{ route('guest.catalog') }}" class="btn-primary mt-5 w-full py-3.5 text-base">Beli sekarang</a>
                <p class="mt-3 text-center text-sm text-slate-500">Tanpa daftar, pesanan dipantau dengan kode transaksi. Mau lebih praktis? <a class="link" href="{{ route('register') }}">Daftar gratis</a></p>
            @endauth
        </div>
    </div>
</section>

{{-- AGEN --}}
<section id="agen" class="relative overflow-hidden bg-ink-900 py-24 text-white">
    <div class="pointer-events-none absolute -left-20 top-0 size-96 rounded-full bg-brand-500/20 blur-3xl"></div>
    <div class="relative mx-auto max-w-6xl px-5">
        <div class="max-w-2xl">
            <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">Punya konter? Ubah jadi penghasilan tambahan.</h2>
            <p class="mt-4 leading-relaxed text-slate-300">Agen mendapat harga khusus, dompet saldo sendiri, dan komisi dari sub-user yang didaftarkan.</p>
        </div>
        <div class="mt-12 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <div class="text-3xl">🏷️</div>
                <h3 class="mt-4 text-lg font-bold">Harga agen</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-300">Modal lebih rendah dari harga pembeli umum, jadi ruang untung lebih lega.</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <div class="text-3xl">👛</div>
                <h3 class="mt-4 text-lg font-bold">Dompet &amp; deposit</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-300">Isi saldo lewat Virtual Account, lalu transaksi kapan saja tanpa bayar satu per satu.</p>
            </div>
            <div class="rounded-2xl border border-brand-500/40 bg-brand-500/15 p-6">
                <div class="text-3xl">👥</div>
                <h3 class="mt-4 text-lg font-bold">Komisi sub-user</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-200">Ajak pelanggan jadi sub-user. Tiap transaksi suksesnya menambah komisi Anda.</p>
            </div>
        </div>
        <div class="mt-10 flex flex-wrap items-center gap-4">
            <a href="{{ route('register-agen') }}" class="btn bg-brand-500 px-7 py-3.5 text-base text-white hover:bg-brand-400">Daftar sebagai agen</a>
            <span class="text-sm text-slate-400">Akun agen aktif setelah disetujui admin.</span>
        </div>
    </div>
</section>

{{-- CARA KERJA --}}
<section id="cara" class="mx-auto max-w-6xl px-5 py-24">
    <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Tiga langkah, selesai.</h2>
    <ol class="relative mt-12 grid gap-8 md:grid-cols-3">
        <li>
            <span class="grid size-12 place-items-center rounded-2xl bg-brand-700 text-xl font-extrabold text-white">1</span>
            <h3 class="mt-5 text-lg font-bold text-slate-900">Pilih &amp; isi nomor</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">Nomor yang salah format langsung ditolak sebelum Anda membayar.</p>
        </li>
        <li>
            <span class="grid size-12 place-items-center rounded-2xl bg-brand-700 text-xl font-extrabold text-white">2</span>
            <h3 class="mt-5 text-lg font-bold text-slate-900">Bayar</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">Pembeli lewat QRIS. Agen langsung potong saldo dompet.</p>
        </li>
        <li>
            <span class="grid size-12 place-items-center rounded-2xl bg-brand-700 text-xl font-extrabold text-white">3</span>
            <h3 class="mt-5 text-lg font-bold text-slate-900">Terima &amp; pantau</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">Status diperbarui otomatis. Kalau gagal, dana atau saldo dikembalikan.</p>
        </li>
    </ol>
</section>

{{-- FAQ --}}
<section id="faq" class="bg-paper py-24">
    <div class="mx-auto max-w-3xl px-5">
        <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">Pertanyaan umum</h2>
        <div class="mt-8 space-y-3">
            <details class="card group px-5 py-4"><summary class="flex cursor-pointer list-none items-center justify-between font-semibold text-slate-900">Bayarnya pakai apa?<span class="text-xl text-brand-700 transition group-open:rotate-45">+</span></summary><p class="mt-3 text-sm leading-relaxed text-slate-600">Pembeli umum bayar lewat QRIS. Agen memakai saldo dompet yang diisi lewat deposit Virtual Account.</p></details>
            <details class="card group px-5 py-4"><summary class="flex cursor-pointer list-none items-center justify-between font-semibold text-slate-900">Kalau transaksi gagal?<span class="text-xl text-brand-700 transition group-open:rotate-45">+</span></summary><p class="mt-3 text-sm leading-relaxed text-slate-600">Dana dikembalikan otomatis: ke saldo untuk agen, atau lewat refund untuk pembayaran pembeli.</p></details>
            <details class="card group px-5 py-4"><summary class="flex cursor-pointer list-none items-center justify-between font-semibold text-slate-900">Format nomor tujuan?<span class="text-xl text-brand-700 transition group-open:rotate-45">+</span></summary><p class="mt-3 text-sm leading-relaxed text-slate-600">Pulsa dan data: 08xxxxxxxx (10-13 digit, +62 otomatis disesuaikan). Token PLN prabayar: nomor meter 11 digit.</p></details>
            <details class="card group px-5 py-4"><summary class="flex cursor-pointer list-none items-center justify-between font-semibold text-slate-900">Apa bedanya akun pembeli dan agen?<span class="text-xl text-brand-700 transition group-open:rotate-45">+</span></summary><p class="mt-3 text-sm leading-relaxed text-slate-600">Agen mendapat harga agen, dompet saldo, sub-user, dan komisi. Pembeli membeli dengan harga umum.</p></details>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="px-5 py-20">
    <div class="mx-auto max-w-6xl overflow-hidden rounded-3xl bg-gradient-to-br from-brand-700 to-ink-900 p-10 text-center text-white sm:p-16">
        <h2 class="mx-auto max-w-2xl text-3xl font-extrabold tracking-tight sm:text-4xl">Siap isi pulsa pertama Anda?</h2>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="btn bg-white px-7 py-3.5 text-base text-ink-900 hover:bg-brand-100">Buka dashboard</a>
            @else
                <a href="{{ route('register') }}" class="btn bg-white px-7 py-3.5 text-base text-ink-900 hover:bg-brand-100">Daftar gratis</a>
                <a href="{{ route('login') }}" class="btn border border-white/30 px-7 py-3.5 text-base text-white hover:bg-white/10">Masuk</a>
            @endauth
        </div>
    </div>
</section>

<footer class="border-t border-slate-200 py-8 text-sm text-slate-500">
    <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-5 sm:flex-row">
        <span>&copy; {{ date('Y') }} {{ $app }}. Semua hak dilindungi.</span>
        <span>Pulsa · Paket data · Token PLN · Game &amp; PPOB</span>
    </div>
</footer>

<script>
(function () {
    var D = {
        pulsa: { lbl: 'Nomor HP tujuan', ph: '08xxxxxxxxxx', items: [[5000,6500],[10000,11500],[25000,26500],[50000,51500],[100000,101500],[200000,201500]] },
        data:  { lbl: 'Nomor HP tujuan', ph: '08xxxxxxxxxx', items: [['3 GB',22000],['8 GB',45000],['15 GB',70000],['30 GB',110000]] },
        pln:   { lbl: 'Nomor meter (11 digit)', ph: '12345678901', items: [[20000,22500],[50000,52500],[100000,102500],[200000,202500]] }
    };
    var chips = document.getElementById('chips'), total = document.getElementById('total'),
        tabs = document.getElementById('tabs'), no = document.getElementById('no'), lbl = document.getElementById('lbl');
    var rp = function (n) { return 'Rp ' + n.toLocaleString('id-ID'); };
    var go = document.getElementById('go'), goBase = go ? go.getAttribute('href') : '', cur = 'pulsa';
    function updateGo() {
        if (!go || go.id !== 'go' || goBase.indexOf('/beli') === -1) return;
        var digits = (no.value || '').replace(/\D/g, '');
        go.href = goBase + '?jenis=' + cur + (digits && cur !== 'pln' ? '&number=' + digits : '');
    }
    function show(k) {
        cur = k; updateGo();
        var d = D[k]; lbl.textContent = d.lbl; no.placeholder = d.ph; total.textContent = rp(0);
        chips.innerHTML = '';
        d.items.forEach(function (it) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'rounded-xl border border-slate-300 py-3 text-sm font-semibold text-slate-700 transition hover:border-brand-600';
            b.textContent = typeof it[0] === 'number' ? it[0].toLocaleString('id-ID') : it[0];
            b.onclick = function () {
                chips.querySelectorAll('button').forEach(function (x) { x.className = x.className.replace(' bg-brand-700 text-white border-brand-700', ''); });
                b.className += ' bg-brand-700 text-white border-brand-700';
                total.textContent = rp(it[1]);
            };
            chips.appendChild(b);
        });
        tabs.querySelectorAll('button').forEach(function (t) {
            var on = t.dataset.k === k;
            t.className = 'rounded-lg py-2 ' + (on ? 'bg-white shadow-sm' : 'text-slate-500');
        });
    }
    tabs.addEventListener('click', function (e) { if (e.target.dataset.k) show(e.target.dataset.k); });
    no.addEventListener('input', updateGo);
    show('pulsa');
})();
</script>
</body>
</html>
