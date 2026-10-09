@php
    $tabs = [
        ['Ringkasan', route('admin.content.index'), request()->routeIs('admin.content.index')],
        ['Pusat Bantuan (FAQ)', route('admin.content.faqs'), request()->routeIs('admin.content.faqs*')],
        ['Kebijakan Privasi', route('admin.content.page', 'privacy-policy'), request()->is('admin/konten/halaman/privacy-policy')],
        ['Syarat & Ketentuan', route('admin.content.page', 'terms'), request()->is('admin/konten/halaman/terms')],
        ['Tentang & Kontak', route('admin.content.app-info'), request()->routeIs('admin.content.app-info*')],
        ['Logo Aplikasi', route('admin.content.logo'), request()->routeIs('admin.content.logo*')],
    ];
@endphp
<div class="mb-6 flex gap-2 overflow-x-auto pb-1">
    @foreach ($tabs as [$label, $url, $on])
        <a href="{{ $url }}" @if ($on) aria-current="page" @endif class="chip {{ $on ? 'chip-on' : '' }}">{{ $label }}</a>
    @endforeach
</div>
