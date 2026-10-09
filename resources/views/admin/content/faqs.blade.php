@extends('layouts.app')
@section('title', 'Pusat Bantuan (FAQ)')

@section('content')
@include('admin.content._tabs')

<form method="POST" action="{{ route('admin.content.faqs.store') }}" class="card p-5 mb-6 space-y-3">
    @csrf
    <div class="font-semibold">Tambah pertanyaan</div>
    <div>
        <label class="label" for="new_question">Pertanyaan</label>
        <input id="new_question" name="question" value="{{ old('_new') ? old('question') : '' }}" class="field" required maxlength="255">
        @if (old('_new')) @error('question')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror @endif
    </div>
    <div>
        <label class="label" for="new_answer">Jawaban</label>
        <textarea id="new_answer" name="answer" rows="3" class="field" required>{{ old('_new') ? old('answer') : '' }}</textarea>
        @if (old('_new')) @error('answer')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror @endif
    </div>
    <input type="hidden" name="_new" value="1">
    <div class="flex items-center justify-between gap-3">
        <span class="text-xs text-slate-500">Ditaruh paling bawah; urutan bisa diubah setelah disimpan.</span>
        <button class="btn-primary">Tambah</button>
    </div>
</form>

<div class="space-y-4">
@forelse ($faqs as $faq)
    <div class="card p-5">
        <form method="POST" action="{{ route('admin.content.faqs.update', $faq) }}" class="space-y-3">
            @csrf @method('PUT')
            <div>
                <label class="label" for="q{{ $faq->id }}">Pertanyaan</label>
                <input id="q{{ $faq->id }}" name="question" value="{{ old('_id') == $faq->id ? old('question') : $faq->question }}" class="field" required maxlength="255">
                @if (old('_id') == $faq->id) @error('question')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror @endif
            </div>
            <div>
                <label class="label" for="a{{ $faq->id }}">Jawaban</label>
                <textarea id="a{{ $faq->id }}" name="answer" rows="3" class="field" required>{{ old('_id') == $faq->id ? old('answer') : $faq->answer }}</textarea>
                @if (old('_id') == $faq->id) @error('answer')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror @endif
            </div>
            <input type="hidden" name="_id" value="{{ $faq->id }}">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-4 text-sm">
                    <label class="flex items-center gap-2">
                        Urutan
                        <input type="number" name="sort_order" min="0" value="{{ $faq->sort_order }}" class="field field-sm w-20">
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked($faq->is_active)> Tampil di aplikasi
                    </label>
                </div>
                <div class="flex items-center gap-2">
                    <button class="btn-primary btn-sm">Simpan</button>
                    <button type="submit" form="del{{ $faq->id }}" class="btn-secondary btn-sm"
                            onclick="return confirm('Hapus pertanyaan ini?')">Hapus</button>
                </div>
            </div>
        </form>
        <form id="del{{ $faq->id }}" method="POST" action="{{ route('admin.content.faqs.destroy', $faq) }}" class="hidden">
            @csrf @method('DELETE')
        </form>
    </div>
@empty
    <div class="card p-10 text-center text-slate-500">Belum ada FAQ. Tambahkan pertanyaan pertama di atas.</div>
@endforelse
</div>
@endsection
