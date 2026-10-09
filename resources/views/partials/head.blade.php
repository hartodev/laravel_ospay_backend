@if (\App\Support\AppBranding::hasCustomLogo())
<link rel="icon" href="{{ \App\Support\AppBranding::webLogo() }}">
<link rel="apple-touch-icon" href="{{ \App\Support\AppBranding::webLogo() }}">
@else
<link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/logo-192.png') }}">
@endif
{{-- Sistem desain bersama. Tailwind via CDN (tanpa build step); untuk produksi pindahkan ke Vite + Tailwind. --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                colors: {
                    brand: {
                        50: '#eff5ff', 100: '#dbe8ff', 200: '#bcd4ff', 300: '#8db6ff', 400: '#5a93fa',
                        500: '#2f6df0', 600: '#2459dc', 700: '#1d47b8', 800: '#1c3c92', 900: '#1b3374',
                    },
                    ink: { 700: '#1f3f8f', 800: '#1a3482', 900: '#132a6b' },
                    paper: '#f3f6fd',
                },
                boxShadow: {
                    card: '0 1px 2px rgb(19 42 107 / .05), 0 8px 24px -12px rgb(36 89 220 / .18)',
                },
            },
        },
    };
</script>
<style type="text/tailwindcss">
    @layer base {
        html { font-feature-settings: 'tnum' 1, 'cv11' 1; }
        body { @apply bg-paper text-slate-800 antialiased; }
        :focus-visible { @apply outline-2 outline-offset-2 outline-brand-500; }
    }
    @layer components {
        .card { @apply bg-white rounded-3xl border border-slate-200/70 shadow-card; }
        .card-pad { @apply p-5 sm:p-6; }

        .btn { @apply inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-semibold transition-colors disabled:opacity-60 disabled:cursor-not-allowed; }
        .btn-primary { @apply btn bg-brand-600 text-white hover:bg-brand-700 shadow-sm shadow-brand-600/25; }
        .btn-secondary { @apply btn bg-white text-slate-700 border border-slate-300 hover:bg-slate-50; }
        .btn-danger { @apply btn bg-white text-red-700 border border-red-200 hover:bg-red-50; }
        .btn-sm { @apply px-3 py-1.5 text-xs rounded-lg; }

        .field { @apply w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 transition focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15; }
        .field-sm { @apply rounded-lg px-2.5 py-1.5; }
        .label { @apply block text-sm font-medium text-slate-700 mb-1.5; }

        .table-wrap { @apply card overflow-x-auto; }
        .table { @apply w-full text-sm; }
        .table thead th { @apply bg-slate-50/80 text-left text-xs font-semibold text-slate-500 px-4 py-3 whitespace-nowrap border-b border-slate-200; }
        .table tbody td { @apply px-4 py-3.5 align-middle; }
        .table tbody tr { @apply border-b border-slate-100 last:border-0 hover:bg-slate-50/60; }
        .table .empty { @apply px-4 py-12 text-center text-slate-500; }


        .tile { @apply grid place-items-center rounded-2xl bg-white text-brand-600 shadow-card ring-1 ring-slate-200/60 transition group-hover:-translate-y-0.5 group-hover:text-brand-700; }
        .chip { @apply inline-flex shrink-0 items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-brand-300 hover:text-brand-700; }
        .chip-on { @apply border-brand-500 bg-brand-500 text-white shadow-md shadow-brand-500/25 hover:border-brand-500 hover:text-white; }

        .stat-label { @apply text-sm text-slate-500; }
        .stat-value { @apply mt-1.5 text-2xl font-bold tracking-tight text-slate-900; }

        .link { @apply font-semibold text-brand-700 hover:text-brand-800 hover:underline underline-offset-4; }

        .dl-row { @apply flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-4 px-5 py-3.5; }
        .dl-row dt, .dl-row .dl-key { @apply sm:w-40 shrink-0 text-slate-500; }
    }
</style>


@include('partials.theme')
