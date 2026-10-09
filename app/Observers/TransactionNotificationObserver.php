<?php

namespace App\Observers;

use App\Jobs\SendTransactionSuccessPush;
use App\Models\Transactions;

/**
 * Kirim push "Transaksi Berhasil" tepat saat status berubah jadi success
 * (callback IAK, cek status manual, atau job cron - semuanya lewat
 * $transaction->update([...]) di TransactionService).
 *
 * $afterCommit = true: observer baru jalan setelah DB::transaction commit,
 * jadi notifikasi tidak terkirim untuk transaksi yang akhirnya di-rollback.
 */
class TransactionNotificationObserver
{
    public bool $afterCommit = true;

    public function updated(Transactions $transaction): void
    {
        if (
            $transaction->wasChanged('status')
            && $transaction->status === 'success'
        ) {
            SendTransactionSuccessPush::dispatch($transaction->id)->afterResponse();
        }
    }
}
