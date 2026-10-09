<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\Transactions;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(protected TransactionService $transactionService) {}

    public function index(Request $request)
    {
        $transactions = $request->user()->transactions()
            ->with('product')
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('transactions.index', compact('transactions'));
    }

    /**
     * Form beli: ?product={product_code}
     */
    public function create(Request $request)
    {
        $product = Products::where('product_code', $request->query('product'))
            ->where('status', 'active')
            ->firstOrFail();

        return view('transactions.create', [
            'product' => $product,
            'price' => $product->priceForRole($request->user()->role),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_code' => ['required', 'exists:products,product_code'],
            'customer_id' => ['required', 'string', 'max:50'],
        ]);

        $product = Products::where('product_code', $data['product_code'])
            ->where('status', 'active')
            ->firstOrFail();

        // Semua logic (wallet vs QRIS, hold saldo, panggil IAK, finalisasi)
        // ada di TransactionService. ValidationException dari service
        // otomatis kembali ke form dengan pesan error.
        $transaction = $this->transactionService->purchase(
            $request->user(),
            $product,
            $data['customer_id']
        );

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('success', 'Transaksi dibuat.');
    }

    public function show(Request $request, Transactions $transaction)
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);

        // Callback IAK/Midtrans tidak bisa masuk di localhost, jadi
        // cek ulang selagi pending (dibatasi 10 detik per transaksi).
        $transaction = $this->transactionService->syncPending($transaction);

        return view('transactions.show', [
            'transaction' => $transaction->load('product'),
        ]);
    }

    /**
     * Cek ulang status ke IAK (kalau callback belum masuk).
     */
    public function refresh(Request $request, Transactions $transaction)
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);

        $result = $this->transactionService->refreshStatus($transaction);

        if ($result->status === 'pending') {
            $message = $result->payment_status === 'pending'
                ? 'Pembayaran QRIS belum diterima Midtrans.'
                : 'Pembayaran diterima, transaksi masih diproses supplier (IAK).';
        } else {
            $message = 'Status diperbarui: ' . $result->status;
        }

        return back()->with('success', $message);
    }
}
