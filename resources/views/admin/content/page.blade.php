@extends('layouts.app')
@section('title', $pageName)

@section('content')
@include('admin.content._tabs')

<form method="POST" action="{{ route('admin.content.page.update', $slug) }}" class="card p-5 sm:p-6 space-y-5">
    @csrf @method('PUT')

    @if (! $page)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Halaman ini belum ada di database. Isi lalu simpan untuk menampilkannya di aplikasi.
        </div>
    @endif

    @include('partials.input', ['name' => 'title', 'label' => 'Judul halaman', 'required' => true, 'value' => $page->title ?? $pageName])

    <div>
        <label class="label" for="intro">Pengantar (di bawah judul)</label>
        <textarea id="intro" name="intro" rows="3" class="field @error('intro') !border-red-400 @enderror">{{ old('intro', $page->intro ?? '') }}</textarea>
        @error('intro')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="label" for="sections_text">Isi halaman</label>
        <textarea id="sections_text" name="sections_text" rows="22" required spellcheck="true"
                  class="field font-mono text-[13px] leading-relaxed @error('sections_text') !border-red-400 @enderror">{{ old('sections_text', $sectionsText) }}</textarea>
        @error('sections_text')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        <p class="mt-2 text-xs text-slate-500 leading-relaxed">
            Tulis judul bagian dengan awalan <code class="rounded bg-slate-100 px-1">## </code>, lalu paragraf di bawahnya.
            Pisahkan paragraf dengan satu baris kosong. Contoh:<br>
            <code class="block mt-1 rounded bg-slate-100 px-2 py-1.5 whitespace-pre">## Keamanan Data

Kami menjaga datamu dengan langkah yang wajar.

Jangan bagikan kode verifikasi kepada siapa pun.</code>
        </p>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <label class="flex items-center gap-2 text-sm">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $page->is_active ?? true))> Tampilkan di aplikasi
        </label>
        <div class="flex items-center gap-3">
            @if ($page?->updated_at)
                <span class="text-xs text-slate-400">Terakhir diubah {{ $page->updated_at->format('d M Y H:i') }}</span>
            @endif
            <button class="btn-primary">Simpan</button>
        </div>
    </div>
</form>
@endsection
