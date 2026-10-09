<?php

namespace App\Console\Commands;

use App\Models\Deposits;
use Illuminate\Console\Command;

class ExpireStaleDeposits extends Command
{
    protected $signature = 'deposits:expire-stale';

    protected $description = 'Tandai deposit QRIS pending yang tidak pernah dapat callback sebagai expired.';

    /**
     * Ambang waktu deposit dianggap basi.
     *
     * QRIS Midtrans biasanya expired dalam waktu singkat di sisi
     * Midtrans sendiri (callback expired akan datang duluan dalam
     * kondisi normal). Command ini murni fallback kalau callback
     * expired dari Midtrans tidak pernah sampai ke server kita.
     */
    protected int $staleMinutes = 60;

    public function handle(\App\Services\DepositService $depositService): int
    {
        $deposits = Deposits::query()
            ->where('status', 'pending')
            ->where('created_at', '<=', now()->subMinutes($this->staleMinutes))
            ->limit(100)
            ->get();

        $this->info("Menemukan {$deposits->count()} deposit pending basi (> {$this->staleMinutes} menit).");

        foreach ($deposits as $deposit) {
            // Jangan expire-kan deposit yang ternyata sudah dibayar tapi callback-nya hilang.
            $deposit = $depositService->syncWithMidtrans($deposit);

            if ($deposit->status !== 'pending') {
                $this->line("Deposit #{$deposit->id} ({$deposit->transaction_number}) status Midtrans: {$deposit->status}.");
                continue;
            }

            $deposit->update(['status' => 'expired']);

            $this->line("Deposit #{$deposit->id} ({$deposit->transaction_number}) ditandai expired.");
        }

        return self::SUCCESS;
    }
}
