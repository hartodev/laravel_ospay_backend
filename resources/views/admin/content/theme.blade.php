@extends('layouts.app')
@section('title', 'Warna Aplikasi')

@section('content')
@include('admin.content._tabs')

<div class="grid gap-6 lg:grid-cols-[1fr_20rem] items-start">
    <div class="space-y-4">
        <form method="POST" action="{{ route('admin.content.theme.update') }}" class="card p-5 sm:p-6 space-y-5">
            @csrf @method('PUT')

            <div>
                <div class="font-semibold">Warna aplikasi mobile</div>
                <p class="text-xs text-slate-500">
                    Pilih dua warna. Warna turunan (lebih gelap, lebih pucat, warna teks) dihitung otomatis.
                    Aplikasi memakai warna baru saat dibuka lagi atau kembali dari background.
                </p>
            </div>

            @foreach ([
                ['primary', 'Warna utama', 'Tombol, header, dan kartu saldo.'],
                ['secondary', 'Warna kedua', 'Aksen pendukung.'],
            ] as [$name, $label, $hint])
                <div>
                    <label class="label" for="{{ $name }}">{{ $label }}</label>
                    <p class="mb-2 text-xs text-slate-500">{{ $hint }}</p>
                    <div class="flex items-center gap-3">
                        <input type="color" id="{{ $name }}_picker" value="{{ old($name, $theme[$name]) }}"
                               aria-label="Pilih {{ strtolower($label) }}"
                               class="h-11 w-14 cursor-pointer rounded-lg border border-slate-200 bg-white p-1">
                        <input id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $theme[$name]) }}"
                               maxlength="7" required placeholder="{{ $defaults[$name] }}" autocomplete="off"
                               class="field max-w-[10rem] font-mono uppercase @error($name) !border-red-400 @enderror">
                    </div>
                    @error($name)<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
            @endforeach

            <p id="contrast-note" class="hidden text-xs font-medium text-amber-600">
                Warna utama cukup terang: teks putih di tombol dan header bisa sulit dibaca.
            </p>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                <button type="submit" form="theme-reset"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                    Kembalikan ke warna awal
                </button>
                <button class="btn-primary">Simpan</button>
            </div>
        </form>

        <form id="theme-reset" method="POST" action="{{ route('admin.content.theme.reset') }}"
              onsubmit="return confirm('Kembalikan warna aplikasi ke warna awal?')">
            @csrf
        </form>

        <p class="text-xs text-slate-500">
            Versi tema saat ini: <span class="font-mono">{{ $theme['version'] }}</span>.
            Nomor ini naik setiap kali Anda menyimpan, dan dipakai aplikasi untuk tahu ada warna baru.
        </p>
    </div>

    {{-- Pratinjau --}}
    <aside class="card p-4 lg:sticky lg:top-6">
        <div class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Pratinjau</div>
        <div id="theme-preview" class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50"
             style="--primary: {{ old('primary', $theme['primary']) }}; --secondary: {{ old('secondary', $theme['secondary']) }};">
            <div class="p-4 text-white"
                 style="background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 78%, black), var(--primary), color-mix(in srgb, var(--primary) 85%, white));">
                <div class="text-xs opacity-80">Saldo</div>
                <div class="text-xl font-bold">Rp 1.250.000</div>
            </div>
            <div class="space-y-3 p-4">
                <div class="flex gap-2">
                    <span class="rounded-full px-3 py-1 text-xs font-semibold text-white" style="background: var(--secondary)">Pulsa</span>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold"
                          style="color: var(--primary); background: color-mix(in srgb, var(--primary) 12%, white)">Data</span>
                </div>
                <div class="rounded-xl py-2.5 text-center text-sm font-semibold text-white" style="background: var(--primary)">Beli Sekarang</div>
            </div>
        </div>
        <p class="mt-3 text-xs text-slate-500">Hanya gambaran. Tampilan asli di aplikasi bisa sedikit berbeda.</p>
    </aside>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var hex = /^#[0-9a-f]{6}$/i;
    var preview = document.getElementById('theme-preview');
    var note = document.getElementById('contrast-note');

    function luminance(h) {
        var c = [1, 3, 5].map(function (i) {
            var v = parseInt(h.substr(i, 2), 16) / 255;
            return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
        });
        return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
    }

    function bind(name) {
        var picker = document.getElementById(name + '_picker');
        var text = document.getElementById(name);

        function apply(v) {
            if (!hex.test(v)) return;
            preview.style.setProperty('--' + name, v);
            if (name === 'primary') {
                note.classList.toggle('hidden', 1.05 / (luminance(v) + 0.05) >= 3);
            }
        }

        picker.addEventListener('input', function () {
            text.value = picker.value.toUpperCase();
            apply(picker.value);
        });
        text.addEventListener('input', function () {
            var v = text.value.trim();
            if (v && v.charAt(0) !== '#') v = '#' + v;
            if (hex.test(v)) { picker.value = v; apply(v); }
        });
        apply(text.value.trim());
    }

    bind('primary');
    bind('secondary');
})();
</script>
@endpush
