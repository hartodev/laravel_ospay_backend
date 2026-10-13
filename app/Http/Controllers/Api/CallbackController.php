<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deposits;
use App\Models\Transactions;
use App\Services\DepositService;
use App\Services\Midtrans\CallbackService;
use App\Services\TransactionService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CallbackController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
        private WalletService $walletService,
        private DepositService $depositService
    ) {}

    /**
     * ================================================================
     * MIDTRANS CALLBACK
     * ================================================================
     */
    public function midtrans(
        Request $request
    ): JsonResponse {
        $callback = new CallbackService($request);

        Log::info(
            'Midtrans callback received',
            $callback->payload()
        );

        if (! $callback->isSignatureKeyVerified()) {
            Log::warning(
                'Invalid Midtrans callback signature',
                $callback->payload()
            );

            return response()->json([
                'success' => false,
                'message' => 'Invalid signature.',
            ], 403);
        }

        $orderId = $callback->orderId();

        if (! $orderId) {
            return response()->json([
                'success' => false,
                'message' => 'Order ID tidak ditemukan.',
            ], 422);
        }

        $transaction = Transactions::where(
            'midtrans_order_id',
            $orderId
        )->first();

        if (! $transaction) {
            $deposit = $callback->getDeposit();

            if ($deposit) {
                return $this->handleDepositCallback(
                    $callback,
                    $deposit
                );
            }

            // Akun Midtrans bisa dipakai aplikasi lain. Notifikasi yang
            // order_id-nya bukan milik OSPay diabaikan, tapi dijawab 200
            // supaya Midtrans tidak mengirim ulang.
            Log::info(
                'Midtrans callback diabaikan: order_id bukan milik OSPay',
                [
                    'order_id' => $orderId,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Ignored: order not found in this application.',
            ]);
        }

        $status = $callback->transactionStatus();

        if ($callback->isSuccess()) {
            return $this->handlePaymentSuccess(
                $transaction
            );
        }

        if ($callback->isExpire()) {
            return $this->handlePaymentExpired(
                $transaction
            );
        }

        if ($callback->isCancelled()) {
            return $this->handlePaymentCancelled(
                $transaction
            );
        }

        if ($status === 'pending') {
            $transaction = Transactions::findOrFail(
                $transaction->id
            );

            if (
                $transaction->payment_status !== 'paid'
                && $transaction->status !== 'success'
            ) {
                $transaction->update([
                    'payment_status' => 'pending',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment masih pending.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Callback diterima.',
            'status' => $status,
        ]);
    }

    /**
     * ================================================================
     * PAYMENT SUCCESS
     * ================================================================
     */
    protected function handlePaymentSuccess(
        Transactions $transaction
    ): JsonResponse {
        DB::transaction(function () use ($transaction) {
            $lockedTransaction = Transactions::where(
                'id',
                $transaction->id
            )
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedTransaction->status === 'success') {
                return;
            }

            if (
                $lockedTransaction->status === 'failed'
                && $lockedTransaction->payment_status === 'paid'
            ) {
                return;
            }

            $lockedTransaction->update([
                'payment_status' => 'paid',
            ]);
        });

        $transaction->refresh();

        if (
            in_array(
                $transaction->status,
                [
                    'success',
                    'failed',
                ],
                true
            )
        ) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction already finalized.',
                'data' => [
                    'transaction_id' =>
                    $transaction->id,

                    'payment_status' =>
                    $transaction->payment_status,

                    'status' =>
                    $transaction->status,
                ],
            ]);
        }

        $transaction = $this->transactionService
            ->processPaidTransaction(
                $transaction
            );

        return response()->json([
            'success' => true,
            'message' => 'Payment berhasil diproses.',
            'data' => [
                'transaction_id' =>
                $transaction->id,

                'payment_status' =>
                $transaction->payment_status,

                'status' =>
                $transaction->status,
            ],
        ]);
    }

    /**
     * ================================================================
     * PAYMENT EXPIRED
     * ================================================================
     */
    protected function handlePaymentExpired(
        Transactions $transaction
    ): JsonResponse {
        $transaction = DB::transaction(
            function () use ($transaction) {
                $lockedTransaction = Transactions::where(
                    'id',
                    $transaction->id
                )
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $lockedTransaction->status === 'success'
                ) {
                    return $lockedTransaction;
                }

                if (
                    $lockedTransaction->payment_status === 'paid'
                ) {
                    return $lockedTransaction;
                }

                $lockedTransaction->update([
                    'payment_status' => 'expired',
                    'status' => 'failed',
                ]);

                return $lockedTransaction;
            }
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment expired.',
            'data' => [
                'transaction_id' =>
                $transaction->id,

                'payment_status' =>
                $transaction->payment_status,

                'status' =>
                $transaction->status,
            ],
        ]);
    }

    /**
     * ================================================================
     * PAYMENT CANCELLED / DENIED
     * ================================================================
     */
    protected function handlePaymentCancelled(
        Transactions $transaction
    ): JsonResponse {
        $transaction = DB::transaction(
            function () use ($transaction) {
                $lockedTransaction = Transactions::where(
                    'id',
                    $transaction->id
                )
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $lockedTransaction->status === 'success'
                ) {
                    return $lockedTransaction;
                }

                if (
                    $lockedTransaction->payment_status === 'paid'
                ) {
                    return $lockedTransaction;
                }

                $lockedTransaction->update([
                    'payment_status' => 'failed',
                    'status' => 'failed',
                ]);

                return $lockedTransaction;
            }
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment cancelled or denied.',
            'data' => [
                'transaction_id' =>
                $transaction->id,

                'payment_status' =>
                $transaction->payment_status,

                'status' =>
                $transaction->status,
            ],
        ]);
    }

    /**
     * ================================================================
     * DEPOSIT CALLBACK
     * ================================================================
     *
     * Semua business logic (idempotency, update status, credit wallet)
     * sudah dipindah ke DepositService - controller cuma delegasi dan
     * menyusun response.
     */
    protected function handleDepositCallback(
        CallbackService $callback,
        Deposits $deposit
    ): JsonResponse {
        if ($callback->isSuccess()) {
            $deposit = $this->depositService->markSuccess($deposit);

            return response()->json([
                'success' => true,
                'message' => 'Deposit berhasil diproses.',
                'data' => [
                    'deposit_id' => $deposit->id,
                    'status' => $deposit->status,
                ],
            ]);
        }

        if ($callback->isExpire()) {
            $this->depositService->markExpired($deposit);

            return response()->json([
                'success' => true,
                'message' => 'Deposit payment expired.',
            ]);
        }

        if ($callback->isCancelled()) {
            $this->depositService->markCancelled($deposit);

            return response()->json([
                'success' => true,
                'message' =>
                'Deposit payment cancelled or denied.',
            ]);
        }

        if (
            $callback->transactionStatus() === 'pending'
        ) {
            $this->depositService->markPending($deposit);

            return response()->json([
                'success' => true,
                'message' =>
                'Deposit payment masih pending.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Deposit callback diterima.',
            'status' =>
            $callback->transactionStatus(),
        ]);
    }
}
