@php
    // Pusat kategori: jenis produk di database dikelompokkan seperti layar "Semua Layanan" di mobile.
    // [kata kunci pada products.type, label tile, kelompok, warna]. Jenis yang tidak cocok masuk "Lainnya".
    $rules = [
        [['roaming'],                    'Roaming',        'Pulsa & Paket',     'orange'],
        [['esim', 'intl'],               'eSIM Intl',      'Pulsa & Paket',     'sky'],
        [['telepon', 'telp', 'sms'],     'Telepon & SMS',  'Pulsa & Paket',     'violet'],
        [['data', 'paket'],              'Paket Data',     'Pulsa & Paket',     'teal'],
        [['pulsa'],                      'Pulsa',          'Pulsa & Paket',     'indigo'],
        [['pln', 'listrik', 'token'],    'PLN',            'Tagihan & Energi',  'amber'],
        [['pgn', 'gas'],                 'Gas PGN',        'Tagihan & Energi',  'red'],
        [['pdam', 'air'],                'PDAM',           'Tagihan & Energi',  'sky'],
        [['bpjs'],                       'BPJS',           'Tagihan & Energi',  'emerald'],
        [['toll', 'money'],              null,             'Tagihan & Energi',  'violet'],
        [['game'],                       'Game',           'Hiburan',           'indigo'],
        [['voucher'],                    'Voucher',        'Hiburan',           'pink'],
        [['stream'],                     'Streaming',      'Hiburan',           'red'],
    ];
    $tints = [
        'indigo' => 'bg-indigo-50 text-indigo-500',   'teal'    => 'bg-teal-50 text-teal-500',
        'violet' => 'bg-violet-50 text-violet-500',   'orange'  => 'bg-orange-50 text-orange-500',
        'amber'  => 'bg-amber-50 text-amber-500',     'red'     => 'bg-red-50 text-red-500',
        'pink'   => 'bg-pink-50 text-pink-500',       'sky'     => 'bg-sky-50 text-sky-500',
        'emerald'=> 'bg-emerald-50 text-emerald-500',
    ];
    $spare = array_keys($tints);

    $groups = ['Pulsa & Paket' => [], 'Tagihan & Energi' => [], 'Hiburan' => [], 'Lainnya' => []];
    foreach ($types as $i => $type) {
        $lower = strtolower($type);
        $hit = null;
        foreach ($rules as $rule) {
            foreach ($rule[0] as $key) {
                if (str_contains($lower, $key)) { $hit = $rule; break 2; }
            }
        }
        $label = $hit[1] ?? ucwords($lower);
        $group = $hit[2] ?? 'Lainnya';
        $color = $hit[3] ?? $spare[$i % count($spare)];
        $groups[$group][] = ['type' => $type, 'label' => $label, 'tint' => $tints[$color]];
    }
@endphp

<div class="grid gap-4 lg:grid-cols-2">
    @foreach ($groups as $title => $items)
        @continue(empty($items))
        <section class="card p-5 sm:p-6">
            <h2 class="mb-5 flex items-center gap-2.5 font-bold tracking-tight text-slate-900">
                <span class="h-5 w-1.5 rounded-full bg-brand-500"></span>{{ $title }}
            </h2>
            <div class="grid grid-cols-4 gap-x-3 gap-y-5">
                @foreach ($items as $it)
                    <a href="{{ route('products.index', ['type' => $it['type']]) }}" class="group flex flex-col items-center gap-2 text-center text-sm text-slate-600">
                        <span class="grid size-16 place-items-center rounded-2xl transition group-hover:-translate-y-0.5 group-hover:shadow-md {{ $it['tint'] }}">@include('partials.icon', ['for' => $it['type'], 'class' => 'size-6'])</span>
                        <span class="leading-tight">{{ $it['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach
</div>
