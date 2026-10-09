@extends('layouts.app')
@section('title', 'Konten Aplikasi')

@section('content')
@include('admin.content._tabs')

<p class="mb-5 text-sm text-slate-500">
    Isi di bawah ini tampil di menu <b>Akun</b> pada aplikasi mobile. Perubahan langsung berlaku;
    pengguna cukup membuka ulang halamannya, tanpa update aplikasi.
</p>

<div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
    <a href="{{ route('admin.content.faqs') }}" class="card p-5 hover:border-brand-300 transition">
        <div class="text-sm text-slate-500">Pusat Bantuan (FAQ)</div>
        <div class="stat-value">{{ $faqActive }}<span class="text-base font-medium text-slate-400"> / {{ $faqTotal }} aktif</span></div>
        <div class="mt-2 text-xs link">Kelola pertanyaan &rarr;</div>
    </a>

    @foreach ($pageNames as $slug => $name)
        @php $p = $pages[$slug] ?? null; @endphp
        <a href="{{ route('admin.content.page', $slug) }}" class="card p-5 hover:border-brand-300 transition">
            <div class="text-sm text-slate-500">{{ $name }}</div>
            <div class="stat-value text-lg">
                @if ($p) {{ count($p->sections ?? []) }} bagian @else <span class="text-amber-600">Belum ada</span> @endif
            </div>
            <div class="text-xs text-slate-400 mt-1">
                @if ($p) {{ $p->is_active ? 'Aktif' : 'Nonaktif' }} &middot; diubah {{ $p->updated_at?->format('d M Y H:i') }} @else Tidak tampil di aplikasi @endif
            </div>
            <div class="mt-2 text-xs link">Edit &rarr;</div>
        </a>
    @endforeach

    <a href="{{ route('admin.content.app-info') }}" class="card p-5 hover:border-brand-300 transition">
        <div class="text-sm text-slate-500">Tentang Aplikasi &amp; Kontak</div>
        <div class="stat-value text-lg">{{ $info ? count($info['highlights'] ?? []).' poin unggulan' : 'Belum diatur' }}</div>
        <div class="text-xs text-slate-400 mt-1">Versi terbaru: {{ $info['latest_version'] ?? '-' }}</div>
        <div class="mt-2 text-xs link">Edit &rarr;</div>
    </a>
</div>
@endsection
