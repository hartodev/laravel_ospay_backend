<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transactions;
use App\Services\Iak\CallbackService;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IakCallbackController extends Controller
{
    /**
     * ================================================================
     * CONSTRUCTOR
     * ================================================================
     */
    public function __construct(
        private TransactionService $transactionService
    ) {}

    /**
     * ================================================================
     * IAK CALLBACK
     * ================================================================
     *
     * Endpoint PUBLIC karena dipanggil oleh server IAK.
     *
     * Flow:
     *
     * IAK
     *   ↓
     * IakCallbackController
     *   ↓
     * Validate Signature
     *   ↓
     * Cari Transaction berdasarkan ref_id
     *   ↓
     * TransactionService::finalize()
     *   ↓
     * ┌───────────┬───────────┬───────────┐
     * │ SUCCESS   │ FAILED    │ PENDING   │
     * │           │           │           │
     * │ User      │ User      │ tetap     │
     * │ selesai   │ refund    │ pending   │
     * │           │           │           │
     * │ Agen      │ Agen      │ hold      │
     * │ commit    │ release   │ tetap     │
     * └───────────┴───────────┴───────────┘
     */
    public function __invoke(
        Request $request
    ): JsonResponse {
        $payload = $request->all();

        /*
     * IAK selalu membungkus field transaksi di dalam key "data":
     * { "data": { "ref_id": ..., "sign": ..., "status": ..., ... } }
     * Unwrap dulu sebelum baca ref_id/sign/status.
     */
        $data = $payload['data'] ?? $payload;

        Log::info(
            'IAK callback received',
            [
                'ref_id' => $data['ref_id'] ?? null,
                'status' => $data['status'] ?? null,
                'rc' => $data['rc'] ?? null,
            ]
        );

        $callback = new CallbackService();

        if (! $callback->isSignatureValid($data)) {
            Log::warning(
                'Invalid IAK callback signature.',
                ['ref_id' => $data['ref_id'] ?? null]
            );

            return response()->json([
                'success' => false,
                'message' => 'Invalid signature.',
            ], 403);
        }

        $refId = $data['ref_id'] ?? null;

        if (! $refId) {
            return response()->json([
                'success' => false,
                'message' => 'ref_id is required.',
            ], 422);
        }

        $transaction = Transactions::where('ref_id', $refId)->first();

        if (! $transaction) {
            Log::warning(
                'IAK callback transaction not found.',
                ['ref_id' => $refId]
            );

            return response()->json([
                'success' => false,
                'message' => 'Transaction not found.',
            ], 200);
        }

        if (
            $transaction->payment_method === 'midtrans'
            && $transaction->payment_status !== 'paid'
        ) {
            Log::warning(
                'IAK callback received before Midtrans payment.',
                [
                    'transaction_id' => $transaction->id,
                    'ref_id' => $refId,
                    'payment_status' => $transaction->payment_status,
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Transaction payment not confirmed.',
            ], 200);
        }

        if (
            in_array(
                $transaction->status,
                ['success', 'failed'],
                true
            )
        ) {
            Log::info(
                'IAK callback ignored because transaction is already final.',
                [
                    'transaction_id' => $transaction->id,
                    'ref_id' => $refId,
                    'status' => $transaction->status,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaction already finalized.',
            ]);
        }

        $transaction = $this->transactionService->finalize(
            $transaction,
            [
                'data' => $data,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'transaction_id' => $transaction->id,
                'ref_id' => $transaction->ref_id,
                'status' => $transaction->status,
                'payment_status' => $transaction->payment_status,
            ],
        ]);
    }
}
