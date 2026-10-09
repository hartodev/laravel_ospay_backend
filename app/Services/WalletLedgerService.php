<?php

namespace App\Services;

use App\Models\WalletTransaction;
use App\Models\Wallets;

/**
 * Pencatat ledger — INSERT-ONLY, tidak pernah mengubah saldo.
 *
 * Perubahan `balance` harus SUDAH dilakukan oleh pemanggil (WalletService),
 * di dalam DB transaction yang sama, SEBELUM method record() ini dipanggil.
 *
 * CATATAN: ledger ini hanya mencatat pergerakan kolom `balance`, bukan
 * `held_balance`. Lihat penjelasan di WalletService::hold() dan
 * ::release() kenapa dua method itu tidak menulis ledger.
 */
class WalletLedgerService
{
    public function record(
        Wallets $wallet,
        string $type,
        int $amount,
        int $balanceBefore,
        int $balanceAfter,
        string $referenceType,
        int $referenceId,
        ?string $description = null,
    ): WalletTransaction {
        return WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => $type,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'description' => $description,
        ]);
    }
}
