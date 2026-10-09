<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\GuestCheckoutService;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Beli tanpa login dari landing page. Pembeli melacak pesanan dengan kode transaksi.
 */
class GuestCheckoutController extends Controller
{
    /** Kata kunci dari landing -> jenis produk di database (pencocokan LIKE). */
    private const KEYWORDS = ['pulsa', 'data', 'pln', 'game', 'voucher'];

    public function __construct(
        private GuestCheckoutService $guest,
        private TransactionService $transactionService,
    ) {}

    // ------------------------------------------------------------ katalog
    public function catalog(Request $request): View|RedirectResponse
    {
        // Yang sudah login membeli lewat akunnya (harga & riwayat mengikuti akun).
        if ($request->user()) {
            return redirect()->route('products.index', $request->only('type', 'brand'));
        }

        $base = fn () => $this->guest->buyable();

        $types = $base()->whereNotNull('type')->distinct()->orderBy('type')->pluck('type');

        $type = $request->query('type');
        if (! $type && ($kw = strtolower((string) $request->query('jenis'))) && in_array($kw, self::KEYWORDS, true)) {
            $type = $types->first(fn ($t) => str_contains(strtolower($t), $kw));
        }
        $brand = $request->query('brand');
        $t = strtolower((string) $type);

        $brands = $type
            ? $base()->where('type', $type)->whereNotNull('brand')
                ->selectRaw('brand, COUNT(*) as total')->groupBy('brand')->orderBy('brand')->get()
            : collect();

        $products = $base()
            ->when($type, fn ($q, $v) => $q->where('type', $v))
            ->when($brand, fn ($q, $v) => $q->where('brand', $v))
            ->when($request->q, function ($q, $v) {
                $q->where(function ($q) use ($v) {
                    $q->where('name', 'like', "%{$v}%")
                        ->orWhere('brand', 'like', "%{$v}%")
                        ->orWhere('product_code', 'like', "%{$v}%");
                });
            })
            ->orderBy('type')->orderBy('brand')->orderBy('price_guest')
            ->paginate(24)
            ->withQueryString();

        return view('guest.catalog', [
            'products' => $products,
            'types' => $types,
            'type' => $type,
            'brand' => $brand,
            'brands' => $brands,
            'showNumber' => $t !== '' && (str_contains($t, 'pulsa') || str_contains($t, 'data')),
            'gridMode' => $t !== '' && (bool) preg_match('/pln|voucher|game|esim/', $t),
            'brandLabel' => str_contains($t, 'game') ? 'Game' : 'Provider',
        ]);
    }

    // --------------------------------------------------------- form + beli
    public function product(Request $request, string $product): View|RedirectResponse
    {
        if ($request->user()) {
            return redirect()->route('transactions.create', ['product' => $product]);
        }

        return view('guest.product', [
            'product' => $this->guest->findProduct($product),
        ]);
    }

    public function store(Request $request, string $product): RedirectResponse
    {
        abort_if((bool) $request->user(), 403);

        $data = $request->validate([
            'customer_id' => ['required', 'string', 'max:50'],
        ], [
            'customer_id.required' => 'Nomor tujuan wajib diisi.',
        ]);

        $transaction = $this->guest->checkout($product, $data['customer_id']);

        return redirect()
            ->route('guest.status', $transaction->ref_id)
            ->with('success', 'Pesanan dibuat. Simpan kode transaksi Anda lalu bayar lewat QRIS.');
    }

    // -------------------------------------------------------------- lacak
    public function track(): View
    {
        return view('guest.track');
    }

    public function lookup(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:60']], [
            'code.required' => 'Masukkan kode transaksi.',
        ]);

        $transaction = $this->guest->find($data['code']);

        if (! $transaction) {
            throw ValidationException::withMessages([
                'code' => 'Kode transaksi tidak ditemukan. Periksa lagi penulisannya.',
            ]);
        }

        return redirect()->route('guest.status', $transaction->ref_id);
    }

    public function status(string $code): View
    {
        $transaction = $this->guest->findOrFail($code);

        // Callback Midtrans/IAK bisa terlambat, jadi cek ulang selagi pending
        // (dibatasi 10 detik per transaksi di service).
        $transaction = $this->transactionService->syncPending($transaction);

        return view('guest.status', [
            'transaction' => $transaction->load('product'),
        ]);
    }

    public function refresh(string $code): RedirectResponse
    {
        $transaction = $this->guest->findOrFail($code);
        $result = $this->transactionService->refreshStatus($transaction);

        if ($result->status === 'pending') {
            $message = $result->payment_status === 'pending'
                ? 'Pembayaran QRIS belum diterima.'
                : 'Pembayaran diterima, pesanan masih diproses.';
        } else {
            $message = 'Status diperbarui: '.$result->status;
        }

        return redirect()->route('guest.status', $transaction->ref_id)->with('success', $message);
    }
}
