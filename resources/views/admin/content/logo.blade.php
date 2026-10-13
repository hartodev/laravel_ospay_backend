@extends('layouts.app')
@section('title', 'Logo Aplikasi')

@section('content')
@include('admin.content._tabs')

<div class="grid gap-6 lg:grid-cols-[1fr_20rem] items-start">
    <div class="space-y-4">
        <form method="POST" action="{{ route('admin.content.logo.update') }}" enctype="multipart/form-data"
              class="card p-5 sm:p-6 space-y-5">
            @csrf

            <div>
                <div class="font-semibold">Logo aplikasi (web dan mobile)</div>
                <p class="text-xs text-slate-500">
                    Satu logo dipakai di web (navbar, halaman login, landing, dan ikon tab browser)
                    dan di aplikasi mobile (layar pembuka, login, daftar, beranda).
                    Web langsung berubah setelah disimpan; aplikasi mobile memakainya saat dibuka lagi atau kembali dari background.
                </p>
            </div>

            <div>
                <label class="label" for="logo">File logo</label>
                <input id="logo" name="logo" type="file" required
                       accept="image/png,image/jpeg,image/webp"
                       class="field @error('logo') !border-red-400 @enderror">
                @error('logo')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                <p class="mt-2 text-xs text-slate-500">
                    PNG, JPG, atau WEBP. Maksimal 2 MB, minimal 128 x 128 piksel.
                    Disarankan gambar persegi (1:1), mis. 512 x 512, karena logo ditampilkan dalam kotak berujung membulat.
                </p>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                <button type="submit" form="logo-reset" @disabled(! $hasLogo)
                        class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
                    Kembalikan ke logo bawaan
                </button>
                <button class="btn-primary">Simpan logo</button>
            </div>
        </form>

        <form id="logo-reset" method="POST" action="{{ route('admin.content.logo.reset') }}"
              onsubmit="return confirm('Kembalikan logo aplikasi ke logo bawaan?')">
            @csrf @method('DELETE')
        </form>

        @php $canPack = $iconSupported && ($hasLogo || count($customIcons) > 0); @endphp
        <div class="card p-5 sm:p-6 space-y-4">
            <div>
                <div class="font-semibold">Ikon aplikasi Android (mipmap)</div>
                <p class="text-xs text-slate-500">
                    Ganti ikon tiap ukuran langsung dari sini. Ukuran yang tidak diganti dibuat otomatis dari logo di atas.
                    Ikon launcher tertanam di APK, jadi setelah ikon diubah, unduh paketnya, taruh di Android Studio, lalu build ulang.
                </p>
            </div>

            @if (! $iconSupported)
                <p class="text-xs font-medium text-red-600">Ekstensi PHP gd dan zip belum aktif di server, jadi ikon belum bisa diproses.</p>
            @endif

            <div class="divide-y divide-slate-100">
                @foreach ($iconSizes as $folder => $px)
                    @php $isCustom = isset($customIcons[$folder]); @endphp
                    <div class="flex flex-wrap items-center gap-3 py-3">
                        <div class="flex size-16 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50">
                            @if ($isCustom || $hasLogo)
                                <img src="{{ route('admin.content.logo.icons', ['d' => $folder, 'v' => $iconVersion.'-'.$version]) }}"
                                     alt="{{ $folder }}" class="max-h-14 max-w-14" style="width: {{ min($px, 56) }}px; height: {{ min($px, 56) }}px">
                            @else
                                <span class="text-[10px] text-slate-400">kosong</span>
                            @endif
                        </div>
                        <div class="min-w-[7rem]">
                            <div class="text-sm font-semibold">{{ str_replace('mipmap-', '', $folder) }} <span class="font-mono text-xs font-normal text-slate-500">{{ $px }} px</span></div>
                            <div class="text-xs {{ $isCustom ? 'text-emerald-600' : 'text-slate-500' }}">
                                {{ $isCustom ? 'Ikon kustom' : ($hasLogo ? 'Otomatis dari logo' : 'Belum ada') }}
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.content.logo.update') }}" enctype="multipart/form-data"
                              class="flex flex-1 flex-wrap items-center gap-2">
                            @csrf
                            <input type="hidden" name="icon_density" value="{{ $folder }}">
                            <input name="icon" type="file" required accept="image/png,image/jpeg,image/webp"
                                   class="field !py-1.5 text-xs min-w-0 flex-1">
                            <button class="btn-primary !px-3 !py-1.5 text-xs" @disabled(! $iconSupported)>Ganti</button>
                        </form>
                        @if ($isCustom)
                            <form method="POST" action="{{ route('admin.content.logo.reset') }}"
                                  onsubmit="return confirm('Hapus ikon kustom ini?')">
                                @csrf @method('DELETE')
                                <input type="hidden" name="icon_density" value="{{ $folder }}">
                                <button class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">Hapus</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
            @error('icon')<p class="text-xs font-medium text-red-600">{{ $message }}</p>@enderror

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ $canPack ? route('admin.content.logo.icons') : '#' }}"
                   @if (! $canPack) aria-disabled="true" onclick="return false" @endif
                   class="btn-primary inline-block {{ $canPack ? '' : 'opacity-40 cursor-not-allowed' }}">
                    Unduh paket ikon Android (ZIP)
                </a>
                @if (count($customIcons) > 0)
                    <form method="POST" action="{{ route('admin.content.logo.reset') }}"
                          onsubmit="return confirm('Hapus semua ikon kustom?')">
                        @csrf @method('DELETE')
                        <input type="hidden" name="icon_density" value="all">
                        <button class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Hapus semua ikon kustom</button>
                    </form>
                @endif
            </div>
            <p class="text-xs text-slate-500">
                Salin isi folder <span class="font-mono">res/</span> ke <span class="font-mono">android/app/src/main/res</span>
                (atau <span class="font-mono">src/dev/res</span> dan <span class="font-mono">src/prod/res</span> kalau pakai flavor), timpa file yang sama, lalu build ulang.
            </p>
            <p class="text-xs text-slate-500">
                Kalau Chrome menampilkan "Download tidak aman diblokir", itu karena alamatnya <span class="font-mono">http://</span>.
                Klik <b>Tetap Download</b>. Di server production yang memakai https peringatan ini tidak muncul.
            </p>
        </div>

        <p class="text-xs text-slate-500">
            Versi logo saat ini: <span class="font-mono">{{ $version }}</span>.
            Nomor ini naik setiap logo diganti, dan dipakai aplikasi untuk tahu ada logo baru.
        </p>
        <p class="text-xs text-slate-500">
            Ikon aplikasi di layar utama HP dan layar pembuka bawaan Android tidak ikut berubah,
            karena keduanya tertanam saat aplikasi dibuat. Kalau tab browser masih menampilkan ikon lama,
            muat ulang dengan Ctrl+F5.
        </p>
    </div>

    <aside class="card p-4 lg:sticky lg:top-6">
        <div class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Logo saat ini</div>
        <div class="flex flex-col items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-6">
            @if ($hasLogo)
                <img id="logo-preview" src="{{ url('/api/app/logo') }}?v={{ $version }}" alt="Logo aplikasi"
                     class="h-24 w-24 rounded-3xl object-cover shadow">
                <span class="text-xs text-slate-500">Logo dari superadmin</span>
            @else
                <div class="flex h-24 w-24 items-center justify-center rounded-3xl bg-slate-200 text-xs text-slate-500">
                    Bawaan
                </div>
                <span class="text-xs text-slate-500">Memakai logo bawaan aplikasi</span>
            @endif
        </div>

        <div class="mt-4 mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Pratinjau pilihan</div>
        <div class="flex items-center justify-center rounded-2xl border border-dashed border-slate-200 p-6">
            <img id="new-preview" alt="" class="hidden h-24 w-24 rounded-3xl object-cover shadow">
            <span id="new-empty" class="text-xs text-slate-400">Belum ada file dipilih</span>
        </div>
    </aside>
</div>

<script>
    document.getElementById('logo').addEventListener('change', function () {
        const file = this.files[0];
        const img = document.getElementById('new-preview');
        const empty = document.getElementById('new-empty');
        if (!file) { img.classList.add('hidden'); empty.classList.remove('hidden'); return; }
        img.src = URL.createObjectURL(file);
        img.classList.remove('hidden');
        empty.classList.add('hidden');
    });
</script>
@endsection
