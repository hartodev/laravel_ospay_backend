@extends('layouts.app')
@section('title', 'Tentang Aplikasi & Kontak')

@section('content')
@include('admin.content._tabs')

<form method="POST" action="{{ route('admin.content.app-info.update') }}" class="space-y-6">
    @csrf @method('PUT')

    <div class="card p-5 sm:p-6 space-y-4">
        <div>
            <div class="font-semibold">Kontak bantuan</div>
            <p class="text-xs text-slate-500">Tampil di halaman Pusat Bantuan.</p>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            @include('partials.input', ['name' => 'whatsapp', 'label' => 'WhatsApp', 'value' => $info['support']['whatsapp'] ?? '', 'placeholder' => '0812-3456-7890'])
            @include('partials.input', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $info['support']['email'] ?? '', 'placeholder' => 'support@ospay.id'])
        </div>
        @include('partials.input', ['name' => 'hours', 'label' => 'Jam operasional', 'value' => $info['support']['hours'] ?? '', 'placeholder' => 'Setiap hari, 08.00 - 21.00 WIB'])
    </div>

    <div class="card p-5 sm:p-6 space-y-4">
        <div>
            <div class="font-semibold">Tentang aplikasi</div>
            <p class="text-xs text-slate-500">Tampil di halaman Tentang Aplikasi.</p>
        </div>
        <div>
            <label class="label" for="description">Deskripsi</label>
            <textarea id="description" name="description" rows="3" required class="field @error('description') !border-red-400 @enderror">{{ old('description', $info['description'] ?? '') }}</textarea>
            @error('description')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
        @include('partials.input', ['name' => 'latest_version', 'label' => 'Versi aplikasi terbaru (opsional)', 'value' => $info['latest_version'] ?? '', 'placeholder' => '1.2.0'])
        <p class="-mt-2 text-xs text-slate-500">
            Kalau lebih tinggi dari versi yang terpasang di HP pengguna, muncul tulisan "Versi terbaru tersedia".
            Kosongkan jika tidak ingin menampilkannya.
        </p>
    </div>

    <div class="card p-5 sm:p-6 space-y-4">
        <div>
            <div class="font-semibold">Poin unggulan</div>
            <p class="text-xs text-slate-500">Maksimal {{ count($rows) }} poin. Baris yang dikosongkan tidak ditampilkan.</p>
        </div>
        @foreach ($rows as $i => $row)
            <div class="rounded-2xl border border-slate-200 p-3 grid gap-3 sm:grid-cols-[11rem_1fr]">
                <div>
                    <label class="label" for="hi{{ $i }}">Ikon</label>
                    <select id="hi{{ $i }}" name="highlights[{{ $i }}][icon]" class="field">
                        @foreach ($icons as $key => $name)
                            <option value="{{ $key }}" @selected(old("highlights.$i.icon", $row['icon'] ?? 'bolt') === $key)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <div>
                        <label class="label" for="ht{{ $i }}">Judul</label>
                        <input id="ht{{ $i }}" name="highlights[{{ $i }}][title]" maxlength="100" value="{{ old("highlights.$i.title", $row['title'] ?? '') }}" class="field @error("highlights.$i.title") !border-red-400 @enderror">
                        @error("highlights.$i.title")<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="hd{{ $i }}">Deskripsi</label>
                        <input id="hd{{ $i }}" name="highlights[{{ $i }}][description]" maxlength="300" value="{{ old("highlights.$i.description", $row['description'] ?? '') }}" class="field">
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex justify-end"><button class="btn-primary">Simpan</button></div>
</form>
@endsection
