<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * GET /api/agen/wallet
     *
     * Menampilkan:
     *
     * balance
     * held_balance
     * available_balance
     */
    public function show(
        Request $request,
        WalletService $walletService
    ) {
        $agent = $request->user();

        $wallet = $walletService->getForAgent(
            $agent
        );

        return response()->json([
            'message' => 'Wallet berhasil diambil.',

            'wallet' => [
                'id' =>
                $wallet->id,

                'balance' =>
                (int) $wallet->balance,

                'held_balance' =>
                (int) $wallet->held_balance,

                'available_balance' =>
                (int) $wallet->available_balance,
            ],
        ]);
    }
}
