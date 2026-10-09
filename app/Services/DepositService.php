<?php

namespace App\Services;

use App\Models\Deposits;
use App\Models\User;
use App\Services\Midtrans\CreateVaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class DepositService
{
    public function __construct(
        private WalletService $walletService,
        private CreateVaService $createVaService
    ) {}

    /**
     * ================================================================
     * CREATE DEPOSIT
     * ================================================================
     *
     * Flow:
     *
     * AGEN
     *   ↓
     * create deposit pending
     *   ↓
     * generate QRIS Midtrans
     *
     * Kalau generate QRIS gagal, deposit langsung di-cancel supaya
     * tidak ada record "pending" yang menggantung tanpa QRIS.
     */
    public function create(User $agent, int $amount): Deposits
    {
        if (! $agent->isAgent()) {
            throw ValidationException::withMessages([
                'role' => ['Hanya agen yang dapat melakukan top up saldo.'],
            ]);
        }

        if (! $agent->isActive()) {
            throw ValidationException::withMessages([
                'user' => ['Akun agen sedang tidak aktif.'],
            ]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['Nominal deposit tidak valid.'],
            ]);
        }

        $deposit = DB::transaction(function () use ($agent, $amount) {
            return Deposits::create([
                'user_id' => $agent->id,

                'transaction_number' =>
                'DP-' . strtoupper(Str::random(12)),

                'amount' => $amount,
                'payment_method' => 'qris',
                'status' => 'pending',
            ]);
        });

        try {
            $response = $this->createVaService->getQrisFor($deposit);
        } catch (Throwable $e) {
            report($e);

            $deposit->update([
                'status' => 'cancelled',
            ]);

            throw ValidationException::withMessages([
                'payment' => ['QRIS top up gagal dibuat.'],
            ]);
        }

        $qrUrl = CreateVaService::extractQrUrl($response);

        $deposit->update([
            'qr_url' => $qrUrl,
            'midtrans_response' => $response,
        ]);

        return $deposit->fresh();
    }

    /**
     * ================================================================
     * SYNC STATUS DARI MIDTRANS
     * ================================================================
     *
     * Cadangan untuk callback: tanya status ke Midtrans lalu terapkan
     * lewat method mark* yang sama dengan callback (idempotent, jadi
     * aman jika callback juga datang belakangan).
     *
     * Tidak pernah melempar exception: kalau Midtrans tidak bisa
     * dihubungi, deposit dikembalikan apa adanya.
     */
    public function syncWithMidtrans(Deposits $deposit): Deposits
    {
        if ($deposit->status !== 'pending') {
            return $deposit;
        }

        try {
            $result = $this->createVaService->getStatus(
                $deposit->transaction_number
            );
        } catch (Throwable $e) {
            // 404 = QRIS belum pernah dipindai/terdaftar; jaringan gagal = coba lagi nanti.
            return $deposit;
        }

        $status = $result['transaction_status'] ?? null;

        if ($status === 'settlement' || $status === 'capture') {
            if (($result['fraud_status'] ?? null) === 'deny') {
                return $this->markCancelled($deposit);
            }

            // Jangan kreditkan kalau nominal di Midtrans tidak sama.
            if ((int) round((float) ($result['gross_amount'] ?? 0)) !== (int) $deposit->amount) {
                report(new \RuntimeException(
                    "Nominal Midtrans tidak cocok untuk deposit {$deposit->transaction_number}"
                ));

                return $deposit;
            }

            return $this->markSuccess($deposit);
        }

        if ($status === 'expire') {
            return $this->markExpired($deposit);
        }

        if ($status === 'cancel' || $status === 'deny') {
            return $this->markCancelled($deposit);
        }

        return $deposit;
    }

    /**
     * ================================================================
     * MARK SUCCESS
     * ================================================================
     *
     * Dipanggil dari Midtrans callback.
     *
     * IDEMPOTENT — aman dipanggil berkali-kali untuk deposit yang sama
     * (callback Midtrans bisa datang lebih dari sekali).
     */
    public function markSuccess(Deposits $deposit): Deposits
    {
        return DB::transaction(function () use ($deposit) {
            $locked = Deposits::where('id', $deposit->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === 'success') {
                return $locked->fresh();
            }

            $locked->update([
                'status' => 'success',
                'paid_at' => now(),
            ]);

            $this->walletService->credit(
                $locked->user,
                (int) $locked->amount,
                'deposit',
                $locked->id
            );

            return $locked->fresh();
        });
    }

    /**
     * ================================================================
     * MARK EXPIRED
     * ================================================================
     */
    public function markExpired(Deposits $deposit): Deposits
    {
        return DB::transaction(function () use ($deposit) {
            $locked = Deposits::where('id', $deposit->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === 'success') {
                return $locked->fresh();
            }

            $locked->update([
                'status' => 'expired',
            ]);

            return $locked->fresh();
        });
    }

    /**
     * ================================================================
     * MARK CANCELLED
     * ================================================================
     */
    public function markCancelled(Deposits $deposit): Deposits
    {
        return DB::transaction(function () use ($deposit) {
            $locked = Deposits::where('id', $deposit->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === 'success') {
                return $locked->fresh();
            }

            $locked->update([
                'status' => 'cancelled',
            ]);

            return $locked->fresh();
        });
    }

    /**
     * ================================================================
     * MARK PENDING
     * ================================================================
     */
    public function markPending(Deposits $deposit): Deposits
    {
        return DB::transaction(function () use ($deposit) {
            $locked = Deposits::where('id', $deposit->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === 'success') {
                return $locked->fresh();
            }

            $locked->update([
                'status' => 'pending',
            ]);

            return $locked->fresh();
        });
    }
}
