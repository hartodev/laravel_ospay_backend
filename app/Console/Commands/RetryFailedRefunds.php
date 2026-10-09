<?php

namespace App\Console\Commands;

use App\Models\Transactions;
use App\Services\Midtrans\RefundService;
use Illuminate\Console\Command;

class RetryFailedRefunds extends Command
{
    protected $signature = 'transactions:retry-refund';

    protected $description = 'Retry refund Midtrans untuk transaksi dengan refund_status=failed.';

    public function handle(RefundService $refundService): int
    {
        $transactions = Transactions::query()
            ->where('refund_status', 'failed')
            ->where('payment_method', 'midtrans')
            ->limit(50)
            ->get();

        $this->info("Menemukan {$transactions->count()} refund gagal untuk di-retry.");

        foreach ($transactions as $transaction) {
            $refundService->process($transaction);

            $transaction->refresh();

            $this->line(
                "Transaction #{$transaction->id} (ref_id {$transaction->ref_id}) -> refund_status: {$transaction->refund_status}"
            );
        }

        return self::SUCCESS;
    }
}
