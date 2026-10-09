<?php

namespace App\Observers;

use App\Services\CommissionService;

/**
 * Mencatat komisi tepat saat status transaksi berubah menjadi "success"
 * (lewat callback IAK, job cron, atau cek status manual).
 *
 * Catatan: observer hanya jalan kalau status diubah lewat Eloquent
 * ($transaction->update([...])), bukan lewat query builder langsung.
 */
class TransactionCommissionObserver
{
    public function updated($transaction): void
    {
        if ($transaction->wasChanged('status') && $transaction->status === 'success') {
            app(CommissionService::class)->recordFor($transaction);
        }
    }
}
