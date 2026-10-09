<?php

namespace App\Http\Controllers\Api;

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
        return $request->user()->transactions()->with('product')->latest()->paginate(15);
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
        // sudah ada di TransactionService - controller cuma delegasi, jangan
        // duplikat logic itu di sini lagi.
        $transaction = $this->transactionService->purchase(
            $request->user(),
            $product,
            $data['customer_id']
        );

        return response()->json(['transaction' => $transaction], 201);
    }

    public function show(Request $request, Transactions $transaction)
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);

        return response()->json($transaction->load('product'));
    }
}
