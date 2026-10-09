<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Saldo agen: balance, held_balance, available_balance + mutasi terakhir.
     */
    public function show(Request $request, WalletService $walletService)
    {
        $wallet = $walletService->getForAgent($request->user());

        return view('agen.wallet', [
            'wallet' => $wallet,
            'ledger' => $wallet->transactions()->latest()->paginate(15),
        ]);
    }
}
