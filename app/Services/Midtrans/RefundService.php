<?php

namespace App\Services\Midtrans;

use App\Models\Transactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Transaction as MidtransTransaction;
use Throwable;

class RefundService extends Midtrans
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ================================================================
     * PROCESS REFUND
     * ================================================================
     *
     * Dipanggil begitu transaksi USER gagal di IAK (refund_status
     * sudah di-set 'pending' oleh TransactionService sebelum ini
     * dipanggil).
     *
     * Hanya berlaku untuk transaksi midtrans (QRIS/gopay acquirer),
     * karena hanya kanal ini yang didukung refund API Midtrans.
     *
     * TIDAK melempar exception ke pemanggil - kegagalan refund
     * dicatat sebagai refund_status='failed' + refund_reason,
     * supaya callback IAK tetap sukses direspon meski refund gagal.
     * Retry manual/scheduler bisa memproses ulang transaksi dengan
     * refund_status='failed' di kemudian hari (Phase 10).
     */
    public function process(Transactions $transaction): Transactions
    {
        if ($transaction->payment_method !== 'midtrans') {
            return $transaction;
        }

        if (! in_array($transaction->refund_status, ['pending', 'failed'], true)) {
            return $transaction;
        }

        if (! $transaction->midtrans_order_id) {
            Log::warning('Refund dilewati: midtrans_order_id kosong.', [
                'transaction_id' => $transaction->id,
            ]);

            return $transaction;
        }

        try {
            $response = MidtransTransaction::refund(
                $transaction->midtrans_order_id,
                [
                    'refund_key' => 'refund-' . $transaction->id,
                    'amount' => (int) $transaction->refund_amount,
                    'reason' => $transaction->failure_reason ?? 'Transaksi PPOB gagal diproses.',
                ]
            );

            $result = json_decode(json_encode($response), true);

            return DB::transaction(function () use ($transaction, $result) {
                $locked = Transactions::where('id', $transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $locked->update([
                    'refund_status' => 'refunded',
                    'refunded_at' => now(),
                    'midtrans_response' => $result,
                ]);

                return $locked->fresh();
            });
        } catch (Throwable $e) {
            report($e);

            Log::error('Refund Midtrans gagal.', [
                'transaction_id' => $transaction->id,
                'order_id' => $transaction->midtrans_order_id,
                'error' => $e->getMessage(),
            ]);

            return DB::transaction(function () use ($transaction, $e) {
                $locked = Transactions::where('id', $transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $locked->update([
                    'refund_status' => 'failed',
                    'refund_reason' => $e->getMessage(),
                ]);

                return $locked->fresh();
            });
        }
    }
}
