@extends('layouts.app')

@section('title', 'Tema Warna')
@section('heading', 'Tema Warna Aplikasi')

@section('content')
    @php
        $fields = [
            'primary' => ['Warna utama', 'Header, tombol utama, kartu saldo'],
            'primary_dark' => ['Utama gelap', 'Ujung gradient'],
            'primary_light' => ['Utama pucat', 'Latar chip & kartu (harus sangat terang)'],
            'on_primary' => ['Teks di atas utama', 'Biasanya putih'],
            'secondary' => ['Warna kedua', 'Aksen'],
            'on_secondary' => ['Teks di atas kedua', 'Biasanya putih'],
        ];
    @endphp

    <div class="max-w-2xl">
        <p class="text-sm text-slate-500 mb-4">
            Warna ini berlaku untuk aplikasi mobile dan dashboard web. Versi saat ini:
            <span class="font-semibold text-slate-900">{{ $theme->version }}</span>.
            Dashboard berubah setelah dimuat ulang, aplikasi mobile saat dibuka berikutnya.
        </p>

        @if (session('status'))
            <div class="mb-4 rounded-2xl bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
        @endif

        <div class="rounded-3xl bg-white p-6 shadow-sm">
            <div id="preview" class="mb-6 flex h-16 items-center justify-center rounded-2xl text-sm font-bold">Pratinjau
                gradient</div>

            <form method="POST" action="{{ route('admin.theme.update') }}" class="space-y-4">
                @csrf

                @foreach ($fields as $name => [$label, $hint])
                    <div>
                        <div class="flex items-center gap-3">
                            <label for="{{ $name }}_text" class="flex-1 text-sm font-semibold text-slate-800">
                                {{ $label }}
                                <span class="block text-xs font-normal text-slate-500">{{ $hint }}</span>
                            </label>
                            <input type="color" id="{{ $name }}_picker" value="{{ old($name, $theme->{$name}) }}"
                                class="h-10 w-12 cursor-pointer rounded-lg border border-slate-200 bg-white p-0.5">
                            <input type="text" id="{{ $name }}_text" name="{{ $name }}" maxlength="7"
                                value="{{ old($name, $theme->{$name}) }}"
                                class="w-28 rounded-xl border border-slate-200 px-3 py-2 font-mono text-sm uppercase">
                        </div>
                        @error($name)
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach

                <button type="submit"
                    class="rounded-2xl bg-brand-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-brand-500/30 hover:opacity-90">
                    Simpan tema
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const names = ['primary', 'primary_dark', 'primary_light', 'on_primary', 'secondary', 'on_secondary'];
            const valid = v => /^#[0-9a-fA-F]{6}$/.test(v);
            const el = id => document.getElementById(id);

            function paint() {
                const a = el('primary_text').value;
                const b = el('primary_dark_text').value;
                const on = el('on_primary_text').value;
                const box = el('preview');
                if (valid(a) && valid(b)) box.style.background = 'linear-gradient(135deg,' + a + ',' + b + ')';
                if (valid(on)) box.style.color = on;
            }

            names.forEach(function(n) {
                const picker = el(n + '_picker');
                const text = el(n + '_text');
                picker.addEventListener('input', function() {
                    text.value = picker.value.toUpperCase();
                    paint();
                });
                text.addEventListener('input', function() {
                    if (valid(text.value)) picker.value = text.value;
                    paint();
                });
            });
            paint();
        })();
    </script>
@endpush
