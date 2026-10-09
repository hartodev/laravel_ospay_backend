@extends('layouts.app') {{-- sesuaikan nama layout superadmin kamu --}}

@section('content')
<div style="max-width:720px;margin:0 auto;padding:24px">
    <h2>Pengaturan Warna Aplikasi</h2>
    <p>Perubahan berlaku untuk web dan aplikasi mobile.</p>

    @if (session('success'))
        <div class="bg-soft" style="padding:12px;border-radius:8px;margin-bottom:16px">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="padding:12px;border-radius:8px;margin-bottom:16px;background:#FEE2E2;color:#991B1B">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Preset --}}
    <h4>Pilih Preset</h4>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px">
        @foreach ($presets as $preset)
            <button type="button"
                    class="preset-btn"
                    data-primary="{{ $preset['primary'] }}"
                    data-secondary="{{ $preset['secondary'] }}"
                    style="display:flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid #ddd;border-radius:999px;background:#fff;cursor:pointer">
                <span style="width:16px;height:16px;border-radius:50%;background:{{ $preset['primary'] }}"></span>
                <span style="width:16px;height:16px;border-radius:50%;background:{{ $preset['secondary'] }}"></span>
                {{ $preset['name'] }}
            </button>
        @endforeach
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('admin.theme.update') }}">
        @csrf
        @method('PUT')

        <div style="display:flex;gap:24px;margin-bottom:16px">
            <div>
                <label for="primary">Warna Utama</label><br>
                <input type="color" id="primary" name="primary"
                       value="{{ old('primary', $current['primary']) }}">
                <code id="primary-text">{{ old('primary', $current['primary']) }}</code>
            </div>
            <div>
                <label for="secondary">Warna Sekunder</label><br>
                <input type="color" id="secondary" name="secondary"
                       value="{{ old('secondary', $current['secondary']) }}">
                <code id="secondary-text">{{ old('secondary', $current['secondary']) }}</code>
            </div>
        </div>

        <button type="submit" class="btn-primary">Simpan</button>
    </form>

    <form method="POST" action="{{ route('admin.theme.reset') }}"
          onsubmit="return confirm('Kembalikan ke warna default?')" style="margin-top:12px">
        @csrf
        <button type="submit" style="background:none;border:0;color:#6B7280;cursor:pointer;text-decoration:underline">
            Reset ke default
        </button>
    </form>

    {{-- Preview --}}
    <h4 style="margin-top:32px">Preview</h4>
    <div style="border:1px solid #e5e7eb;border-radius:16px;overflow:hidden">
        <div id="pv-header" style="padding:16px;font-weight:600">OSPay</div>
        <div style="padding:16px">
            <div id="pv-soft" style="padding:12px;border-radius:10px;margin-bottom:12px">
                Saldo Anda: Rp 1.250.000
            </div>
            <button type="button" id="pv-btn" style="padding:10px 20px;border:0;border-radius:10px">
                Beli Pulsa
            </button>
            <button type="button" id="pv-btn2" style="padding:10px 20px;border:0;border-radius:10px;margin-left:8px">
                Riwayat
            </button>
        </div>
    </div>
</div>

<script>
    const $ = id => document.getElementById(id);
    const rgb = h => { const n = parseInt(h.slice(1), 16); return [(n >> 16) & 255, (n >> 8) & 255, n & 255]; };
    const luma = h => { const [r, g, b] = rgb(h); return (0.299 * r + 0.587 * g + 0.114 * b) / 255; };
    const on = h => luma(h) > 0.6 ? '#111827' : '#FFFFFF';
    const mix = (a, b, w) => {
        const A = rgb(a), B = rgb(b);
        return '#' + A.map((x, i) => Math.round(x * (1 - w) + B[i] * w).toString(16).padStart(2, '0')).join('').toUpperCase();
    };

    function refresh() {
        const p = $('primary').value.toUpperCase();
        const s = $('secondary').value.toUpperCase();

        $('primary-text').textContent = p;
        $('secondary-text').textContent = s;

        $('pv-header').style.background = p;
        $('pv-header').style.color = on(p);

        $('pv-soft').style.background = mix(p, '#FFFFFF', 0.90);
        $('pv-soft').style.color = mix(p, '#000000', 0.15);

        $('pv-btn').style.background = p;
        $('pv-btn').style.color = on(p);

        $('pv-btn2').style.background = s;
        $('pv-btn2').style.color = on(s);
    }

    ['primary', 'secondary'].forEach(id => $(id).addEventListener('input', refresh));

    document.querySelectorAll('.preset-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            $('primary').value = btn.dataset.primary;
            $('secondary').value = btn.dataset.secondary;
            refresh();
        });
    });

    refresh();
</script>
@endsection
