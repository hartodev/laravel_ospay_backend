<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Deposits;
use App\Services\DepositService;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    public function __construct(private DepositService $depositService) {}

    public function index(Request $request)
    {
        return view('agen.deposits.index', [
            'deposits' => $request->user()->deposits()->latest()->paginate(15),
        ]);
    }

    public function create()
    {
        return view('agen.deposits.create');
    }

    /**
     * Top up wallet agen: buat deposit -> QRIS Midtrans -> tampilkan QR.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:10000'],
        ]);

        $deposit = $this->depositService->create($request->user(), (int) $data['amount']);

        return redirect()
            ->route('agen.deposits.show', $deposit)
            ->with('success', 'QRIS top up berhasil dibuat.');
    }

    public function show(Request $request, Deposits $deposit)
    {
        abort_if($deposit->user_id !== $request->user()->id, 403);

        // Cadangan callback Midtrans: cek status langsung selagi masih pending.
        $deposit = $this->depositService->syncWithMidtrans($deposit);

        return view('agen.deposits.show', compact('deposit'));
    }
}