@php
    // Ikon garis 24px. Pakai: @include('partials.icon', ['name' => 'home', 'class' => 'size-5'])
    // atau tebak dari jenis produk: @include('partials.icon', ['for' => 'pulsa telkomsel'])
    $paths = [
        'home'     => 'M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9.5Z',
        'users'    => 'M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1M9.5 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm11.5 8v-1a4 4 0 0 0-3-3.87M15 4.13a3.5 3.5 0 0 1 0 6.74',
        'tag'      => 'M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8ZM7.5 7.5h.01',
        'grid'     => 'M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z',
        'receipt'  => 'M6 3h12v18l-3-2-3 2-3-2-3 2V3Zm3 5h6M9 12h6',
        'wallet'   => 'M3 7a2 2 0 0 1 2-2h13v4M3 7v10a2 2 0 0 0 2 2h14a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1H5a2 2 0 0 1-2-1Zm13 6h.01',
        'download' => 'M12 3v12m0 0-4-4m4 4 4-4M4 20h16',
        'coins'    => 'M12 8c4.4 0 8-1.3 8-3s-3.6-3-8-3-8 1.3-8 3 3.6 3 8 3Zm8 0v4c0 1.7-3.6 3-8 3s-8-1.3-8-3V8m16 4v4c0 1.7-3.6 3-8 3s-8-1.3-8-3v-4',
        'phone'    => 'M7 2h10a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1Zm5 16h.01',
        'wifi'     => 'M2 9a15 15 0 0 1 20 0M5 12.5a10 10 0 0 1 14 0M8.5 16a5 5 0 0 1 7 0M12 19.5h.01',
        'bolt'     => 'M13 2 4 14h7l-1 8 9-12h-7l1-8Z',
        'card'     => 'M3 6h18v12H3V6Zm0 4h18M7 15h3',
        'game'     => 'M6 11h4M8 9v4M15 12h.01M18 10h.01M17.3 5H6.7a4 4 0 0 0-3.9 3.1l-1.4 6.2a2.6 2.6 0 0 0 4.4 2.4L8 14h8l2.2 2.7a2.6 2.6 0 0 0 4.4-2.4l-1.4-6.2A4 4 0 0 0 17.3 5Z',
        'ticket'   => 'M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V8Zm11-2v12',
        'play'     => 'M6 4l14 8-14 8V4Z',
        'dots'     => 'M5 12h.01M12 12h.01M19 12h.01',
        'eye'      => 'M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Zm10 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        'eye-off'  => 'M3 3l18 18M10.6 6.1A9.8 9.8 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4M6.6 6.7C3.9 8.5 2 12 2 12s3.6 7 10 7a9.7 9.7 0 0 0 4.2-.9M9.9 9.9a3 3 0 0 0 4.2 4.2',
        'check'    => 'M20 6 9 17l-5-5',
        'x'        => 'M18 6 6 18M6 6l12 12',
        'clock'    => 'M12 7v5l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'copy'     => 'M9 9h10v10H9V9Zm-4 6V5h10',
        'chevron'  => 'm9 6 6 6-6 6',
        'back'     => 'm15 6-6 6 6 6',
        'logout'   => 'M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3M10 17l5-5-5-5M15 12H3',
        'globe'    => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18ZM3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18',
        'flame'    => 'M12 3s5 4 5 9a5 5 0 0 1-10 0c0-2 1-3 2-4 0 2 1 3 2 3 0-3 1-5 1-8Z',
        'drop'     => 'M12 3s6 6.5 6 11a6 6 0 0 1-12 0c0-4.5 6-11 6-11Z',
        'search'   => 'm21 21-4.3-4.3M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z',
    ];
    if (! isset($name)) {
        $t = strtolower((string) ($for ?? ''));
        $name = match (true) {
            str_contains($t, 'roaming'), str_contains($t, 'esim'), str_contains($t, 'intl') => 'globe',
            str_contains($t, 'pgn'), str_contains($t, 'gas') => 'flame',
            str_contains($t, 'pdam') => 'drop',
            str_contains($t, 'pulsa'), str_contains($t, 'telepon'), str_contains($t, 'sms') => 'phone',
            str_contains($t, 'data') => 'wifi',
            str_contains($t, 'pln'), str_contains($t, 'listrik'), str_contains($t, 'token') => 'bolt',
            str_contains($t, 'toll'), str_contains($t, 'money') => 'card',
            str_contains($t, 'game') => 'game',
            str_contains($t, 'voucher') => 'ticket',
            str_contains($t, 'stream') => 'play',
            default => 'receipt',
        };
    }
@endphp
<svg class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $sw ?? 1.75 }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $paths[$name] ?? $paths['receipt'] }}"/></svg>
