{{-- Halaman Listrik PLN: tab jenis, nomor pelanggan, pilih nominal, bar checkout menempel di bawah. --}}
@php
    $tab = $kind ?? 'prepaid';
    $tabs = [['prepaid', 'Token Listrik'], ['postpaid', 'Tagihan Listrik']];
    $q = fn (array $extra) => request()->fullUrlWithQuery($extra + ['page' => null, 'number' => request('number')]);

    // "PLN 20.000" -> ["20", "rb"]; "PLN 1.000.000" -> ["1", "jt"]; selain itu pakai nama produk apa adanya.
    $denom = function ($name) {
        if (preg_match('/\d[\d.]*/', (string) $name, $m)) {
            $n = (int) str_replace('.', '', $m[0]);
            if ($n >= 1000000 && $n % 1000000 === 0) return [$n / 1000000, 'jt'];
            if ($n >= 1000 && $n % 1000 === 0) return [$n / 1000, 'rb'];
        }
        return [$name, ''];
    };
@endphp

<div class="card overflow-hidden">
    {{-- Tab jenis layanan --}}
    <div class="flex border-b border-slate-200 text-sm font-semibold" role="tablist">
        @foreach ($tabs as [$k, $label])
            <a href="{{ $q(['kind' => $k]) }}" role="tab" aria-selected="{{ $tab === $k ? 'true' : 'false' }}"
               class="relative flex-1 px-4 py-3.5 text-center transition {{ $tab === $k ? 'text-brand-600' : 'text-slate-500 hover:text-slate-800' }}">
                {{ $label }}
                @if ($tab === $k)<span class="absolute inset-x-0 bottom-0 h-0.5 bg-brand-500"></span>@endif
            </a>
        @endforeach
    </div>

    <form id="pln-form" method="GET" action="{{ route('transactions.create') }}" class="p-4 sm:p-6">
        <input type="hidden" name="product" id="pln-product" value="">

        {{-- No. Pelanggan, label mengambang + tombol hapus --}}
        <div class="relative rounded-2xl bg-slate-50 px-4 pb-3 pt-2.5 ring-1 ring-transparent focus-within:bg-white focus-within:ring-brand-500/40">
            <label for="pln-number" class="block text-xs text-slate-500">No. Pelanggan</label>
            <input id="pln-number" name="number" type="text" inputmode="numeric" autocomplete="off" maxlength="20"
                   value="{{ request('number') }}" placeholder="Masukkan nomor meter / ID pelanggan"
                   class="mt-1 w-full border-0 bg-transparent p-0 pr-10 text-lg font-semibold text-slate-900 placeholder:text-base placeholder:font-normal placeholder:text-slate-400 focus:outline-none focus:ring-0">
            <button type="button" id="pln-clear" aria-label="Hapus nomor"
                    class="absolute right-3 top-1/2 hidden size-8 -translate-y-1/2 place-items-center rounded-full text-slate-400 hover:bg-slate-200 hover:text-slate-600">
                @include('partials.icon', ['name' => 'x', 'class' => 'size-4', 'sw' => 2.5])
            </button>
        </div>

        {{-- Pilih nominal --}}
        @if ($products->isEmpty())
            <div class="mt-6 rounded-2xl border border-dashed border-slate-200 p-10 text-center text-slate-500">
                {{ $tab === 'postpaid' ? 'Belum ada produk tagihan listrik untuk saat ini.' : 'Produk token listrik tidak ditemukan.' }}
            </div>
        @else
            <h2 class="mb-3 mt-6 text-sm font-semibold text-slate-700">Pilih nominal</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($products as $p)
                    @php
                        [$big, $unit] = $denom($p->name);
                        $price = $p->priceForRole($role);
                    @endphp
                    <button type="button" data-code="{{ $p->product_code }}" data-name="{{ $p->name }}" data-price="{{ $price }}" aria-pressed="false"
                            class="pln-tile rounded-2xl border border-slate-200 bg-white p-4 text-left transition hover:border-brand-300 focus-visible:outline-brand-500">
                        <span class="block text-3xl font-bold leading-none tracking-tight text-slate-900">{{ $big }}<span class="text-lg font-semibold">{{ $unit }}</span></span>
                        <span class="mt-2 block font-semibold text-brand-600">Rp {{ number_format($price, 0, ',', '.') }}</span>
                    </button>
                @endforeach
            </div>
            <div class="mt-6">{{ $products->links() }}</div>
        @endif
    </form>
</div>

{{-- Bar checkout menempel di bawah. Di layar kecil berada di atas bottom bar navigasi. --}}
<div class="fixed inset-x-0 bottom-[68px] z-20 border-t border-slate-200 bg-white/95 backdrop-blur lg:bottom-0 lg:left-64">
    <div class="mx-auto flex max-w-6xl items-center gap-4 px-4 py-3 sm:px-8">
        <div class="min-w-0 flex-1">
            <div id="pln-sel-name" class="truncate text-xs text-slate-500">Pilih nominal dulu</div>
            <div id="pln-sel-price" class="text-lg font-extrabold tracking-tight text-slate-900">Rp 0</div>
        </div>
        <button type="submit" form="pln-form" id="pln-checkout" disabled class="btn-primary px-8 py-3 text-base">Checkout</button>
    </div>
</div>
<div class="h-20" aria-hidden="true"></div>

@push('scripts')
<script>
    (() => {
        const form = document.getElementById('pln-form'), num = document.getElementById('pln-number');
        const clear = document.getElementById('pln-clear'), prod = document.getElementById('pln-product');
        const go = document.getElementById('pln-checkout');
        const nm = document.getElementById('pln-sel-name'), pr = document.getElementById('pln-sel-price');
        const tiles = [...document.querySelectorAll('.pln-tile')];
        const on = ['!border-brand-500', '!bg-brand-50', 'ring-2', 'ring-brand-500/20'];

        const refresh = () => {
            const digits = num.value.replace(/\D/g, '');
            clear.classList.toggle('hidden', !num.value);
            clear.classList.toggle('grid', !!num.value);
            go.disabled = !(prod.value && digits.length >= 8);
        };
        tiles.forEach((t) => t.addEventListener('click', () => {
            tiles.forEach((o) => { o.classList.remove(...on); o.setAttribute('aria-pressed', 'false'); });
            t.classList.add(...on); t.setAttribute('aria-pressed', 'true');
            prod.value = t.dataset.code;
            nm.textContent = t.dataset.name;
            pr.textContent = 'Rp ' + (+t.dataset.price).toLocaleString('id-ID');
            refresh();
        }));
        num.addEventListener('input', refresh);
        clear.addEventListener('click', () => { num.value = ''; num.focus(); refresh(); });
        form.addEventListener('submit', (e) => { if (go.disabled) e.preventDefault(); else num.value = num.value.replace(/\D/g, ''); });
        refresh();
    })();
</script>
@endpush
