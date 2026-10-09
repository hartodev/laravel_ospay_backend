<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deposits;
use App\Services\DepositService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DepositController extends Controller
{
    public function __construct(
        private DepositService $depositService
    ) {}

    /**
     * Daftar deposit milik agen yang sedang login.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user->isAgent()) {
            throw ValidationException::withMessages([
                'role' => [
                    'Hanya agen yang dapat melihat deposit.'
                ],
            ]);
        }

        return $user
            ->deposits()
            ->latest()
            ->paginate(15);
    }

    /**
     * Membuat top up wallet agen.
     *
     * Semua logic (buat deposit, generate QRIS, error handling)
     * sudah ada di DepositService - controller cuma delegasi.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'amount' => [
                'required',
                'integer',
                'min:10000',
            ],
        ]);

        $deposit = $this->depositService->create(
            $request->user(),
            (int) $data['amount']
        );

        return response()->json([
            'message' => 'QRIS top up berhasil dibuat.',

            'deposit' => $deposit,

            'qr_url' => $deposit->qr_url,
        ], 201);
    }

    /**
     * Detail deposit.
     */
    public function show(
        Request $request,
        Deposits $deposit
    ) {
        $user = $request->user();

        if (! $user->isAgent()) {
            abort(403);
        }

        abort_if(
            $deposit->user_id !== $user->id,
            403
        );

        return response()->json($deposit);
    }
}
