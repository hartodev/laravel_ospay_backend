<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallets;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    public function __construct(
        private WalletLedgerService $walletLedgerService
    ) {}

    public function getForAgent(User $agent): Wallets
    {
        if (! $agent->isAgent()) {
            throw ValidationException::withMessages([
                'user' => ['Wallet hanya tersedia untuk agen.'],
            ]);
        }

        if (! $agent->isActive()) {
            throw ValidationException::withMessages([
                'user' => ['Akun agen tidak aktif.'],
            ]);
        }

        return Wallets::firstOrCreate(
            ['user_id' => $agent->id],
            ['balance' => 0, 'held_balance' => 0]
        );
    }

    /**
     * balance tidak berubah di sini → tidak ada ledger.
     */
    public function hold(User $agent, int $amount): Wallets
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['Nominal harus lebih besar dari 0.'],
            ]);
        }

        if (! $agent->isAgent()) {
            throw ValidationException::withMessages([
                'user' => ['User bukan agen.'],
            ]);
        }

        if (! $agent->isActive()) {
            throw ValidationException::withMessages([
                'user' => ['Akun agen tidak aktif.'],
            ]);
        }

        return DB::transaction(function () use ($agent, $amount) {
            $wallet = Wallets::where('user_id', $agent->id)
                ->lockForUpdate()
                ->first();

            if (! $wallet) {
                throw ValidationException::withMessages([
                    'wallet' => ['Wallet agen belum tersedia.'],
                ]);
            }

            $balance = (int) $wallet->balance;
            $heldBalance = (int) $wallet->held_balance;
            $available = $balance - $heldBalance;

            if ($available < $amount) {
                throw ValidationException::withMessages([
                    'amount' => ['Saldo agen tidak mencukupi.'],
                ]);
            }

            $wallet->held_balance = $heldBalance + $amount;
            $wallet->save();

            return $wallet->fresh();
        });
    }

    /**
     * balance berubah di sini → WAJIB ledger.
     * $referenceType/$referenceId menunjuk transaksi penyebabnya.
     */
    public function commit(
        User $agent,
        int $amount,
        string $referenceType,
        int $referenceId,
        ?string $description = null
    ): Wallets {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['Nominal harus lebih besar dari 0.'],
            ]);
        }

        return DB::transaction(function () use (
            $agent,
            $amount,
            $referenceType,
            $referenceId,
            $description
        ) {
            $wallet = Wallets::where('user_id', $agent->id)
                ->lockForUpdate()
                ->first();

            if (! $wallet) {
                throw ValidationException::withMessages([
                    'wallet' => ['Wallet agen tidak ditemukan.'],
                ]);
            }

            $balance = (int) $wallet->balance;
            $heldBalance = (int) $wallet->held_balance;

            if ($heldBalance < $amount) {
                throw ValidationException::withMessages([
                    'wallet' => ['Held balance tidak mencukupi.'],
                ]);
            }

            if ($balance < $amount) {
                throw ValidationException::withMessages([
                    'wallet' => ['Balance wallet tidak mencukupi.'],
                ]);
            }

            $balanceBefore = $balance;

            $wallet->balance = $balance - $amount;
            $wallet->held_balance = $heldBalance - $amount;
            $wallet->save();

            $this->walletLedgerService->record(
                wallet: $wallet,
                type: 'debit',
                amount: $amount,
                balanceBefore: $balanceBefore,
                balanceAfter: $wallet->balance,
                referenceType: $referenceType,
                referenceId: $referenceId,
                description: $description ?? 'Pembelian PPOB berhasil (agent)',
            );

            return $wallet->fresh();
        });
    }

    /**
     * balance tidak berubah di sini → tidak ada ledger.
     */
    public function release(User $agent, int $amount): Wallets
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['Nominal harus lebih besar dari 0.'],
            ]);
        }

        return DB::transaction(function () use ($agent, $amount) {
            $wallet = Wallets::where('user_id', $agent->id)
                ->lockForUpdate()
                ->first();

            if (! $wallet) {
                throw ValidationException::withMessages([
                    'wallet' => ['Wallet agen tidak ditemukan.'],
                ]);
            }

            $heldBalance = (int) $wallet->held_balance;

            if ($heldBalance < $amount) {
                throw ValidationException::withMessages([
                    'wallet' => ['Held balance tidak mencukupi.'],
                ]);
            }

            $wallet->held_balance = $heldBalance - $amount;
            $wallet->save();

            return $wallet->fresh();
        });
    }

    /**
     * balance berubah di sini → WAJIB ledger.
     * $referenceType/$referenceId menunjuk deposit penyebabnya.
     */
    public function credit(
        User $agent,
        int $amount,
        string $referenceType,
        int $referenceId,
        ?string $description = null
    ): Wallets {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['Nominal deposit tidak valid.'],
            ]);
        }

        if (! $agent->isAgent()) {
            throw ValidationException::withMessages([
                'user' => ['Wallet hanya dapat dikredit untuk agen.'],
            ]);
        }

        if (! $agent->isActive()) {
            throw ValidationException::withMessages([
                'user' => ['Akun agen tidak aktif.'],
            ]);
        }

        return DB::transaction(function () use (
            $agent,
            $amount,
            $referenceType,
            $referenceId,
            $description
        ) {
            $wallet = Wallets::where('user_id', $agent->id)
                ->lockForUpdate()
                ->first();

            if (! $wallet) {
                throw ValidationException::withMessages([
                    'wallet' => ['Wallet agen belum tersedia.'],
                ]);
            }

            $balanceBefore = (int) $wallet->balance;
            $wallet->balance = $balanceBefore + $amount;
            $wallet->save();

            $this->walletLedgerService->record(
                wallet: $wallet,
                type: 'credit',
                amount: $amount,
                balanceBefore: $balanceBefore,
                balanceAfter: $wallet->balance,
                referenceType: $referenceType,
                referenceId: $referenceId,
                description: $description ?? 'Deposit',
            );

            return $wallet->fresh();
        });
    }
}
