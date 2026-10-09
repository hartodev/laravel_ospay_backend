<?php

namespace App\Jobs;

use App\Models\Transactions;
use App\Services\Fcm\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendTransactionSuccessPush implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(public int $transactionId) {}

    public function handle(FcmService $fcm): void
    {
        try {
            $transaction = Transactions::with('product')->find($this->transactionId);

            if (! $transaction || $transaction->status !== 'success') {
                return;
            }

            $productName = $transaction->product?->name ?? 'Transaksi';
            $amount = number_format((int) $transaction->sell_price, 0, ',', '.');

            $fcm->sendToUser(
                (int) $transaction->user_id,
                'Transaksi Berhasil',
                "{$productName} ke {$transaction->customer_id} sebesar Rp {$amount} berhasil.",
                [
                    'type' => 'transaction_success',
                    'transaction_id' => $transaction->id,
                    'ref_id' => $transaction->ref_id,
                    'product_name' => $productName,
                    'customer_id' => $transaction->customer_id,
                    'amount' => (int) $transaction->sell_price,
                    'sn' => $transaction->sn,
                ]
            );
        } catch (Throwable $e) {
            // Notifikasi hanya pelengkap; jangan sampai mengganggu alur lain.
            Log::error('Push transaksi sukses gagal', [
                'transaction_id' => $this->transactionId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
