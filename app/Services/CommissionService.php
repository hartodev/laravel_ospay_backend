<?php

namespace App\Services;

use App\Models\CommissionRule;
use App\Models\Commissions;
use App\Models\Transactions;
use App\Services\WalletService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionService
{
    public function __construct(private WalletService $walletService) {}

    /**
     * ================================================================
     * CREATE COMMISSION FOR TRANSACTION
     * ================================================================
     *
     * Dipanggil setelah transaksi USER (Midtrans) sukses di IAK.
     *
     * Syarat komisi dibuat:
     * - payment_method = midtrans (transaksi USER, bukan agent/wallet)
     * - user pemilik transaksi punya parent_agent_id (didaftarkan oleh agent)
     * - status transaksi = success
     * - ada commission_rule aktif (product-specific, fallback ke general)
     *
     * Nominal:
     * - type percentage -> persentase dari sell_price
     * - type nominal    -> flat amount dari rule->value
     * - di-clamp supaya tidak pernah melebihi margin transaksi
     *   (sell_price - base_price), dan tidak boleh negatif.
     *
     * IDEMPOTENT - aman dipanggil berkali-kali untuk transaksi yang sama.
     * Dilindungi 2 lapis: cek existing row + unique index di DB
     * (kalau ada race condition, insert kedua akan gagal dan kita
     * ambil row yang sudah dibuat oleh proses lain).
     */
    public function createForTransaction(Transactions $transaction): ?Commissions
    {
        if ($transaction->payment_method !== 'midtrans') {
            return null;
        }

        if (! $transaction->isSuccessful()) {
            return null;
        }

        $user = $transaction->user;

        if (! $user || ! $user->parent_agent_id) {
            return null;
        }

        return DB::transaction(function () use ($transaction, $user) {
            $existing = Commissions::where('transaction_id', $transaction->id)->first();

            if ($existing) {
                return $existing;
            }

            $rule = CommissionRule::active()
                ->where('product_id', $transaction->product_id)
                ->first();

            if (! $rule) {
                $rule = CommissionRule::active()
                    ->whereNull('product_id')
                    ->first();
            }

            if (! $rule) {
                Log::info('Komisi tidak dibuat: tidak ada commission_rule aktif.', [
                    'transaction_id' => $transaction->id,
                    'product_id' => $transaction->product_id,
                ]);

                return null;
            }

            $sellPrice = (int) $transaction->sell_price;
            $basePrice = (int) $transaction->base_price;
            $margin = $sellPrice - $basePrice;

            $amount = $rule->type === 'percentage'
                ? (int) round($sellPrice * ((float) $rule->value / 100))
                : (int) $rule->value;

            // Safety clamp: komisi tidak boleh melebihi margin transaksi.
            $amount = max(0, min($amount, max($margin, 0)));

            if ($amount <= 0) {
                Log::info('Komisi dihitung 0 setelah clamp margin, tidak dibuat.', [
                    'transaction_id' => $transaction->id,
                    'rule_id' => $rule->id,
                    'margin' => $margin,
                ]);

                return null;
            }

            try {
                return Commissions::create([
                    'agent_id' => $user->parent_agent_id,
                    'transaction_id' => $transaction->id,
                    'amount' => $amount,
                    'status' => 'pending',
                ]);
            } catch (QueryException $e) {
                // Race condition: proses lain sudah insert duluan
                // di antara pengecekan existing dan insert ini.
                return Commissions::where('transaction_id', $transaction->id)->first();
            }
        });
    }

    /**
     * ================================================================
     * BACKFILL
     * ================================================================
     *
     * Buat komisi untuk transaksi USER (QRIS, sub-user agen) yang sudah
     * sukses tetapi belum punya komisi. Memakai createForTransaction(),
     * jadi semua syarat + idempotency yang sama berlaku.
     *
     * @return array{created:int, skipped:int}
     */
    public function backfill(): array
    {
        $created = 0;
        $skipped = 0;

        Transactions::query()
            ->with('user')
            ->where('status', 'success')
            ->where('payment_method', 'midtrans')
            ->whereHas('user', fn ($q) => $q->whereNotNull('parent_agent_id'))
            ->whereNotIn('id', Commissions::select('transaction_id'))
            ->chunkById(100, function ($chunk) use (&$created, &$skipped) {
                foreach ($chunk as $transaction) {
                    $this->createForTransaction($transaction)
                        ? $created++
                        : $skipped++;
                }
            });

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * ================================================================
     * PAYOUT (CAIRKAN KOMISI)
     * ================================================================
     *
     * Komisi pending -> paid, nominalnya masuk ke saldo wallet agen
     * (tercatat di ledger, reference_type = 'commission').
     * Idempotent: komisi yang sudah paid tidak diproses lagi.
     * Seluruh langkah satu DB transaction + row lock.
     */
    public function payout(Commissions $commission): Commissions
    {
        return DB::transaction(function () use ($commission) {
            $locked = Commissions::whereKey($commission->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === 'paid') {
                return $locked;
            }

            $agent = $locked->agent;

            // Pastikan wallet ada (firstOrCreate), lalu kredit + ledger.
            $this->walletService->getForAgent($agent);
            $this->walletService->credit(
                $agent,
                (int) $locked->amount,
                'commission',
                $locked->id,
                'Komisi transaksi #' . $locked->transaction_id
            );

            $locked->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            return $locked->fresh();
        });
    }

    /**
     * Cairkan semua komisi pending (opsional per agen).
     * Satu komisi gagal tidak menghentikan yang lain.
     *
     * @return array{paid:int, failed:int}
     */
    public function payoutAllPending(?int $agentId = null): array
    {
        $paid = 0;
        $failed = 0;

        Commissions::query()
            ->where('status', 'pending')
            ->when($agentId, fn ($q) => $q->where('agent_id', $agentId))
            ->orderBy('id')
            ->chunkById(100, function ($chunk) use (&$paid, &$failed) {
                foreach ($chunk as $commission) {
                    try {
                        $this->payout($commission);
                        $paid++;
                    } catch (\Throwable $e) {
                        $failed++;
                        Log::warning('Pencairan komisi gagal.', [
                            'commission_id' => $commission->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        return ['paid' => $paid, 'failed' => $failed];
    }
}
