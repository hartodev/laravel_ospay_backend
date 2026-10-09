<?php

namespace App\Console\Commands;

use App\Models\Transactions;
use App\Services\TransactionService;
use Illuminate\Console\Command;

class CheckPendingTransactions extends Command
{
    protected $signature =
    'transactions:check-pending';

    protected $description =
    'Check ulang transaksi IAK yang masih pending.';

    public function handle(
        TransactionService $transactionService
    ): int {
        /*
         * Hanya transaksi yang:
         *
         * 1. status IAK masih pending
         * 2. sudah lebih dari 2 menit
         *
         * yang dicek.
         */
        $pendingTransactions = Transactions::query()
            ->where('status', 'pending')

            ->where(
                'created_at',
                '<=',
                now()->subMinutes(2)
            )

            /*
             * USER:
             * hanya yang sudah paid.
             *
             * AGEN:
             * wallet flow langsung boleh check.
             */
            ->where(function ($query) {
                $query
                    ->where(
                        'payment_method',
                        'wallet'
                    )
                    ->orWhere(function ($query) {
                        $query
                            ->where(
                                'payment_method',
                                'midtrans'
                            )
                            ->where(
                                'payment_status',
                                'paid'
                            );
                    });
            })

            ->limit(100)

            ->get();

        $this->info(
            "Menemukan {$pendingTransactions->count()} "
                . "transaksi IAK pending."
        );

        foreach (
            $pendingTransactions
            as $transaction
        ) {
            $transactionService
                ->refreshStatus($transaction);

            $this->line(
                "Ref ID {$transaction->ref_id} "
                    . "dicek ulang."
            );
        }

        return self::SUCCESS;
    }
}
