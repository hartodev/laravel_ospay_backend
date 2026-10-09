<?php

namespace App\Services;

use App\Models\Products;
use App\Models\Transactions;
use App\Models\User;
use App\Services\Iak\CheckStatusService;
use App\Services\Midtrans\RefundService;
use App\Services\WalletService;
use App\Services\CommissionService;
use App\Services\Iak\TopUpService;
use App\Services\Midtrans\CreateTransactionVaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class TransactionService
{
    public function __construct(
        private WalletService $walletService,
        private TopUpService $topUpService,
        private RefundService $refundService,
        private CheckStatusService $checkStatusService,
        private CreateTransactionVaService $createTransactionVaService,
        private CommissionService $commissionService
    ) {}

    /**
     * ================================================================
     * PURCHASE
     * ================================================================
     */
    public function purchase(
        User $user,
        Products $product,
        string $customerId,
        ?int $sellPriceOverride = null
    ): Transactions {
        $customerId = $this->normalizeCustomerId(
            $product,
            $customerId
        );

        if (! $product->isActive() || ! $product->isSyncedFromIak()) {
            throw ValidationException::withMessages([
                'product' => [
                    'Produk sedang tidak tersedia.',
                ],
            ]);
        }

        if ($user->isUser()) {
            return $this->purchaseWithMidtrans(
                $user,
                $product,
                $customerId,
                $sellPriceOverride
            );
        }

        if ($user->isAgent()) {
            return $this->purchaseWithWallet(
                $user,
                $product,
                $customerId
            );
        }

        throw ValidationException::withMessages([
            'user' => [
                'Role pengguna tidak dapat melakukan pembelian.',
            ],
        ]);
    }

    /**
     * ================================================================
     * USER - MIDTRANS
     * ================================================================
     */
    public function purchaseWithMidtrans(
        User $user,
        Products $product,
        string $customerId,
        ?int $sellPriceOverride = null
    ): Transactions {
        return DB::transaction(function () use (
            $user,
            $product,
            $customerId,
            $sellPriceOverride
        ) {
            $basePrice = (int) $product->base_price;
            // Pembeli tamu memakai harga tamu (price_guest), user terdaftar price_user.
            $sellPrice = $sellPriceOverride ?? (int) $product->price_user;

            $this->assertPriceAboveCost($basePrice, $sellPrice);

            if ($sellPrice <= 0) {
                throw ValidationException::withMessages([
                    'product' => [
                        'Harga produk tidak valid.',
                    ],
                ]);
            }

            $refId = 'TRX-' . strtoupper(
                Str::random(20)
            );

            $midtransOrderId = 'MID-' . $refId;

            $transaction = Transactions::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'customer_id' => $customerId,
                'ref_id' => $refId,
                'midtrans_order_id' => $midtransOrderId,
                'base_price' => $basePrice,
                'sell_price' => $sellPrice,
                'payment_method' => 'midtrans',
                'payment_status' => 'pending',
                'status' => 'pending',
                'refund_status' => 'not_required',
            ]);

            $response = $this->createTransactionVaService
                ->getQris($transaction);

            $qrUrl = CreateTransactionVaService::extractQrUrl(
                $response
            );

            $transaction->update([
                'midtrans_response' => $response,
                'qr_url' => $qrUrl,
            ]);

            return $transaction->fresh();
        });
    }

    /**
     * ================================================================
     * AGEN - WALLET
     * ================================================================
     */
    public function purchaseWithWallet(
        User $agent,
        Products $product,
        string $customerId
    ): Transactions {
        $basePrice = (int) $product->base_price;
        $sellPrice = (int) $product->price_agent;

        $this->assertPriceAboveCost($basePrice, $sellPrice);

        if ($sellPrice <= 0) {
            throw ValidationException::withMessages([
                'product' => [
                    'Harga produk tidak valid.',
                ],
            ]);
        }

        $transaction = DB::transaction(function () use (
            $agent,
            $product,
            $customerId,
            $basePrice,
            $sellPrice
        ) {
            $refId = 'TRX-' . strtoupper(
                Str::random(20)
            );

            $transaction = Transactions::create([
                'user_id' => $agent->id,
                'product_id' => $product->id,
                'customer_id' => $customerId,
                'ref_id' => $refId,
                'base_price' => $basePrice,
                'sell_price' => $sellPrice,
                'payment_method' => 'wallet',
                'payment_status' => 'paid',
                'status' => 'pending',
                'refund_status' => 'not_required',
            ]);

            $this->walletService->hold(
                $agent,
                $sellPrice
            );

            return $transaction;
        });

        return $this->processAgentTransaction(
            $transaction
        );
    }

    /**
     * ================================================================
     * MIDTRANS PAID → IAK
     * ================================================================
     *
     * HANYA dipanggil kalau payment_status = paid.
     *
     * ================================================================
     */
    public function processPaidTransaction(
        Transactions $transaction
    ): Transactions {
        /*
         * Ambil ulang dari database.
         */
        $transaction = Transactions::findOrFail(
            $transaction->id
        );

        /*
         * Kalau sudah final, selesai.
         */
        if (
            in_array(
                $transaction->status,
                [
                    'success',
                    'failed',
                ],
                true
            )
        ) {
            return $transaction->fresh();
        }

        /*
         * Harus transaksi Midtrans.
         */
        if (
            $transaction->payment_method !== 'midtrans'
        ) {
            throw ValidationException::withMessages([
                'transaction' => [
                    'Transaksi bukan transaksi Midtrans.',
                ],
            ]);
        }

        /*
         * Pembayaran harus paid.
         */
        if (
            $transaction->payment_status !== 'paid'
        ) {
            throw ValidationException::withMessages([
                'transaction' => [
                    'Transaksi belum dinyatakan lunas oleh Midtrans.',
                ],
            ]);
        }

        /*
         * ============================================================
         * LOCK TRANSACTION
         * ============================================================
         *
         * Callback duplicate akan menunggu transaksi pertama.
         *
         * Setelah transaksi pertama selesai:
         *
         * success → callback kedua tidak melakukan IAK lagi.
         *
         * pending → callback kedua boleh melakukan retry.
         *
         * failed → callback kedua tidak melakukan IAK lagi.
         *
         * ============================================================
         */
        return DB::transaction(
            function () use ($transaction) {

                $lockedTransaction = Transactions::where(
                    'id',
                    $transaction->id
                )
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                 * Jangan proses ulang transaksi final.
                 */
                if (
                    in_array(
                        $lockedTransaction->status,
                        [
                            'success',
                            'failed',
                        ],
                        true
                    )
                ) {
                    return $lockedTransaction->fresh();
                }

                /*
                 * Pastikan payment tetap paid.
                 */
                if (
                    $lockedTransaction->payment_method !== 'midtrans'
                    || $lockedTransaction->payment_status !== 'paid'
                ) {
                    return $lockedTransaction->fresh();
                }

                try {
                    /*
                     * IAK dipanggil hanya di sini.
                     */
                    $response = $this->topUpService->topUp(
                        (string) $lockedTransaction->customer_id,
                        $this->productCode($lockedTransaction),
                        (string) $lockedTransaction->ref_id
                    );

                    /*
                     * Proses hasil IAK.
                     */
                    return $this->handleIakResponse(
                        $lockedTransaction,
                        $response
                    );
                } catch (Throwable $e) {
                    /*
                     * Jangan menganggap network error sebagai FAILED.
                     *
                     * Bisa saja IAK sudah menerima transaksi.
                     */
                    report($e);

                    Log::error(
                        'IAK top up exception',
                        [
                            'transaction_id' =>
                            $lockedTransaction->id,

                            'ref_id' =>
                            $lockedTransaction->ref_id,

                            'error' =>
                            $e->getMessage(),
                        ]
                    );

                    $lockedTransaction->update([
                        'status' => 'pending',
                        'checked_at' => now(),
                    ]);

                    return $lockedTransaction->fresh();
                }
            }
        );
    }

    /**
     * ================================================================
     * USER / IAK PROCESS
     * ================================================================
     */
    public function processIakTransaction(
        Transactions $transaction
    ): Transactions {
        if (
            in_array(
                $transaction->status,
                [
                    'success',
                    'failed',
                ],
                true
            )
        ) {
            return $transaction->fresh();
        }

        try {
            $response = $this->topUpService->topUp(
                (string) $transaction->customer_id,
                $this->productCode($transaction),
                (string) $transaction->ref_id
            );

            return $this->applyIakResponse(
                $transaction,
                $response
            );
        } catch (Throwable $e) {
            report($e);

            $transaction->update([
                'status' => 'pending',
                'checked_at' => now(),
            ]);

            return $transaction->fresh();
        }
    }

    /**
     * ================================================================
     * AGEN - PROCESS IAK
     * ================================================================
     */
    public function processAgentTransaction(
        Transactions $transaction
    ): Transactions {
        if (
            in_array(
                $transaction->status,
                [
                    'success',
                    'failed',
                ],
                true
            )
        ) {
            return $transaction->fresh();
        }

        try {
            $response = $this->topUpService->topUp(
                (string) $transaction->customer_id,
                $this->productCode($transaction),
                (string) $transaction->ref_id
            );

            return $this->applyIakResponse(
                $transaction,
                $response
            );
        } catch (Throwable $e) {
            report($e);

            $transaction->update([
                'status' => 'pending',
                'checked_at' => now(),
            ]);

            return $transaction->fresh();
        }
    }

    /**
     * ================================================================
     * IAK CALLBACK / FINALIZE
     * ================================================================
     */
    public function finalize(
        Transactions $transaction,
        array $response
    ): Transactions {
        return DB::transaction(
            function () use (
                $transaction,
                $response
            ) {
                /*
                 * LOCK TRANSACTION
                 */
                $lockedTransaction = Transactions::where(
                    'id',
                    $transaction->id
                )
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                 * Final = tidak boleh diproses ulang.
                 */
                if (
                    in_array(
                        $lockedTransaction->status,
                        [
                            'success',
                            'failed',
                        ],
                        true
                    )
                ) {
                    return $lockedTransaction->fresh();
                }

                /*
                 * IAK callback untuk Midtrans hanya boleh
                 * menyelesaikan transaksi kalau Midtrans paid.
                 */
                if (
                    $lockedTransaction->payment_method === 'midtrans'
                    && $lockedTransaction->payment_status !== 'paid'
                ) {
                    return $lockedTransaction->fresh();
                }

                /*
                 * Ambil payload IAK.
                 */
                $iakResponse = $response['data']
                    ?? $response;

                if (! is_array($iakResponse)) {
                    $iakResponse = [
                        'data' => $iakResponse,
                    ];
                }

                /*
                 * Wallet transaction.
                 */
                if (
                    $lockedTransaction->payment_method === 'wallet'
                ) {
                    return $this->handleAgentIakResponse(
                        $lockedTransaction,
                        $iakResponse
                    );
                }

                /*
                 * Midtrans transaction.
                 */
                return $this->handleIakResponse(
                    $lockedTransaction,
                    $iakResponse
                );
            }
        );
    }

    /**
     * ================================================================
     * APPLY IAK RESPONSE (satu pintu, aman dari race)
     * ================================================================
     *
     * Dipakai oleh top-up awal, tombol/halaman cek status, dan command
     * pending. Row transaksi dikunci dulu dan status final dilewati,
     * sehingga callback IAK + cek manual yang datang bersamaan tidak
     * bisa commit/release saldo atau refund dua kali.
     *
     * Panggilan HTTP ke IAK harus SUDAH selesai sebelum method ini.
     */
    protected function applyIakResponse(
        Transactions $transaction,
        array $response
    ): Transactions {
        return DB::transaction(function () use (
            $transaction,
            $response
        ) {
            $locked = Transactions::where('id', $transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                in_array(
                    $locked->status,
                    ['success', 'failed'],
                    true
                )
            ) {
                return $locked;
            }

            return $locked->payment_method === 'wallet'
                ? $this->handleAgentIakResponse($locked, $response)
                : $this->handleIakResponse($locked, $response);
        });
    }

    /**
     * ================================================================
     * HANDLE IAK RESPONSE - USER
     * ================================================================
     */
    protected function handleIakResponse(
        Transactions $transaction,
        array $response
    ): Transactions {
        $status = $this->normalizeIakStatus(
            $response
        );

        /*
         * SUCCESS
         */
        if ($status === 'success') {
            $transaction->update([
                'status' => 'success',

                'sn' => $this->extractSn(
                    $response
                ),

                'failure_reason' => null,

                'iak_response' => $response,

                'checked_at' => now(),
            ]);

            $transaction = $transaction->fresh();

            /*
             * Buat komisi agent kalau transaksi ini milik
             * USER yang didaftarkan oleh agent (parent_agent_id).
             * Tidak berlaku untuk transaksi agent sendiri
             * (payment_method wallet) - sudah difilter
             * di dalam CommissionService.
             */
            $this->commissionService->createForTransaction(
                $transaction
            );

            return $transaction;
        }

        /*
         * FAILED
         *
         * Midtrans sudah paid,
         * tetapi IAK gagal.
         *
         * Maka refund_status = pending.
         */
        if ($status === 'failed') {
            $transaction->update([
                'status' => 'failed',

                'sn' => $this->extractSn(
                    $response
                ),

                'failure_reason' =>
                $this->extractFailureReason(
                    $response
                ),

                'iak_response' => $response,

                'checked_at' => now(),

                'refund_status' => 'pending',

                'refund_amount' =>
                (int) $transaction->sell_price,
            ]);

            $transaction = $transaction->fresh();

            /*
             * Eksekusi refund otomatis ke Midtrans.
             * Tidak melempar exception - kegagalan refund
             * dicatat di refund_status/refund_reason, tidak
             * boleh menggagalkan response callback IAK.
             */
            $transaction = $this->refundService->process(
                $transaction
            );

            return $transaction;
        }

        /*
         * PENDING
         *
         * Jangan refund.
         * Jangan menganggap gagal.
         */
        $transaction->update([
            'status' => 'pending',

            'iak_response' => $response,

            'checked_at' => now(),
        ]);

        return $transaction->fresh();
    }

    /**
     * ================================================================
     * HANDLE IAK RESPONSE - AGEN
     * ================================================================
     */
    protected function handleAgentIakResponse(
        Transactions $transaction,
        array $response
    ): Transactions {
        $status = $this->normalizeIakStatus(
            $response
        );

        /*
         * ============================================================
         * SUCCESS
         * ============================================================
         */
        if ($status === 'success') {
            $lockedTransaction = Transactions::where(
                'id',
                $transaction->id
            )
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * Callback duplicate.
             */
            if (
                in_array(
                    $lockedTransaction->status,
                    [
                        'success',
                        'failed',
                    ],
                    true
                )
            ) {
                return $lockedTransaction->fresh();
            }

            $amount = (int) $lockedTransaction->sell_price;

            $agent = User::findOrFail(
                $lockedTransaction->user_id
            );

            /*
             * HOLD → COMMIT
             */
            $this->walletService->commit(
                $agent,
                $amount,
                'transaction',
                $lockedTransaction->id
            );

            $lockedTransaction->update([
                'status' => 'success',

                'sn' => $this->extractSn(
                    $response
                ),

                'failure_reason' => null,

                'iak_response' => $response,

                'checked_at' => now(),
            ]);

            return $lockedTransaction->fresh();
        }

        /*
         * ============================================================
         * FAILED
         * ============================================================
         */
        if ($status === 'failed') {
            $lockedTransaction = Transactions::where(
                'id',
                $transaction->id
            )
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * Callback duplicate.
             */
            if (
                in_array(
                    $lockedTransaction->status,
                    [
                        'success',
                        'failed',
                    ],
                    true
                )
            ) {
                return $lockedTransaction->fresh();
            }

            $amount = (int) $lockedTransaction->sell_price;

            $agent = User::findOrFail(
                $lockedTransaction->user_id
            );

            /*
             * HOLD → RELEASE
             */
            $this->walletService->release(
                $agent,
                $amount
            );

            $lockedTransaction->update([
                'status' => 'failed',

                'sn' => $this->extractSn(
                    $response
                ),

                'failure_reason' =>
                $this->extractFailureReason(
                    $response
                ),

                'iak_response' => $response,

                'checked_at' => now(),
            ]);

            return $lockedTransaction->fresh();
        }

        /*
         * ============================================================
         * PENDING
         * ============================================================
         *
         * Wallet tetap HOLD.
         */
        $locked = Transactions::where('id', $transaction->id)
            ->lockForUpdate()
            ->firstOrFail();

        // Callback bisa saja sudah menyelesaikan transaksi ini.
        if ($locked->status !== 'pending') {
            return $locked;
        }

        $locked->update([
            'iak_response' => $response,

            'checked_at' => now(),
        ]);

        return $locked->fresh();
    }

    /**
     * ================================================================
     * SYNC PENDING (dipanggil saat halaman detail dibuka)
     * ================================================================
     *
     * Cek ulang transaksi pending ke Midtrans / IAK, tapi dibatasi
     * supaya tidak menembak API eksternal di setiap reload.
     */
    public function syncPending(
        Transactions $transaction,
        int $minSeconds = 10
    ): Transactions {
        if ($transaction->status !== 'pending') {
            return $transaction;
        }

        if (
            $transaction->checked_at
            && $transaction->checked_at->gt(now()->subSeconds($minSeconds))
        ) {
            return $transaction;
        }

        return $this->refreshStatus($transaction);
    }

    /**
     * ================================================================
     * SYNC PEMBAYARAN MIDTRANS (USER / QRIS)
     * ================================================================
     *
     * Padanan callback Midtrans, tapi ditarik dari sisi aplikasi:
     *
     * settlement/capture -> payment_status = paid -> order ke IAK
     * expire             -> payment expired + transaksi failed
     * cancel/deny        -> payment failed  + transaksi failed
     * lainnya (pending)  -> tidak ada perubahan
     *
     * Tidak pernah melempar exception.
     */
    public function syncPaymentWithMidtrans(
        Transactions $transaction
    ): Transactions {
        $transaction = $transaction->fresh();

        if (
            ! $transaction->isMidtransPayment()
            || $transaction->payment_status !== 'pending'
            || $transaction->status !== 'pending'
            || ! $transaction->midtrans_order_id
        ) {
            return $transaction;
        }

        try {
            $result = $this->createTransactionVaService->getStatus(
                (string) $transaction->midtrans_order_id
            );
        } catch (Throwable $e) {
            // 404 = QRIS belum pernah dipindai; 401 = server key salah.
            Log::warning('Sync status Midtrans gagal', [
                'ref_id' => $transaction->ref_id,
                'order_id' => $transaction->midtrans_order_id,
                'error' => $e->getMessage(),
            ]);

            $transaction->update(['checked_at' => now()]);

            return $transaction->fresh();
        }

        $status = $result['transaction_status'] ?? null;

        if ($status === 'settlement' || $status === 'capture') {
            if (($result['fraud_status'] ?? null) === 'deny') {
                return $this->markPaymentFailed($transaction, 'failed');
            }

            // Jangan anggap lunas kalau nominal di Midtrans berbeda.
            if (
                (int) round((float) ($result['gross_amount'] ?? 0))
                !== (int) $transaction->sell_price
            ) {
                report(new \RuntimeException(
                    "Nominal Midtrans tidak cocok untuk {$transaction->ref_id}"
                ));

                return $transaction;
            }

            DB::transaction(function () use ($transaction) {
                $locked = Transactions::where('id', $transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $locked->payment_status === 'pending'
                    && $locked->status === 'pending'
                ) {
                    $locked->update(['payment_status' => 'paid']);
                }
            });

            return $this->processPaidTransaction($transaction);
        }

        if ($status === 'expire') {
            return $this->markPaymentFailed($transaction, 'expired');
        }

        if ($status === 'cancel' || $status === 'deny') {
            return $this->markPaymentFailed($transaction, 'failed');
        }

        $transaction->update(['checked_at' => now()]);

        return $transaction->fresh();
    }

    /**
     * Pembayaran Midtrans expired / dibatalkan / ditolak.
     * Tidak menyentuh transaksi yang sudah paid atau success.
     */
    protected function markPaymentFailed(
        Transactions $transaction,
        string $paymentStatus
    ): Transactions {
        return DB::transaction(function () use (
            $transaction,
            $paymentStatus
        ) {
            $locked = Transactions::where('id', $transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $locked->payment_status === 'paid'
                || $locked->status === 'success'
            ) {
                return $locked;
            }

            $locked->update([
                'payment_status' => $paymentStatus,
                'status' => 'failed',
                'failure_reason' => $paymentStatus === 'expired'
                    ? 'Pembayaran QRIS kedaluwarsa.'
                    : 'Pembayaran QRIS dibatalkan atau ditolak.',
                'checked_at' => now(),
            ]);

            return $locked->fresh();
        });
    }

    /**
     * ================================================================
     * REFRESH STATUS
     * ================================================================
     */
    public function refreshStatus(
        Transactions $transaction
    ): Transactions {
        if ($transaction->status !== 'pending') {
            return $transaction->fresh();
        }

        /*
         * Midtrans belum paid.
         *
         * Jangan panggil IAK.
         */
        if (
            $transaction->payment_method === 'midtrans'
            && $transaction->payment_status !== 'paid'
        ) {
            // Tanya Midtrans langsung (callback tidak bisa masuk di
            // localhost). Kalau ternyata sudah lunas, method ini juga
            // meneruskan order ke IAK.
            return $this->syncPaymentWithMidtrans($transaction);
        }

        try {
            $response = $this->checkStatusService->check(
                (string) $transaction->ref_id
            );

            return $this->applyIakResponse(
                $transaction,
                $response
            );
        } catch (Throwable $e) {
            report($e);

            $transaction->update([
                'checked_at' => now(),
            ]);

            return $transaction->fresh();
        }
    }

    /**
     * ================================================================
     * NORMALIZE CUSTOMER ID
     * ================================================================
     *
     * Produk berbasis nomor HP (pulsa, data, SMS, telepon): buang
     * spasi/tanda hubung, ubah +62 / 62 jadi 0, lalu wajib 08xxxxxxxx
     * dengan total 10-13 digit.
     *
     * Produk lain (PLN, game, dll): hanya cek tidak kosong dan
     * maksimal 50 karakter.
     */
    protected function normalizeCustomerId(
        Products $product,
        string $customerId
    ): string {
        $customerId = trim($customerId);

        if ($customerId === '') {
            throw ValidationException::withMessages([
                'customer_id' => ['Nomor tujuan wajib diisi.'],
            ]);
        }

        $isPhoneProduct = (bool) preg_match(
            '/pulsa|data|sms|telp|telepon|paket|roaming/i',
            (string) $product->type
        );

        // PLN token (prepaid): nomor meter wajib 11 digit angka.
        if (
            $product->category === 'prepaid'
            && preg_match('/pln/i', (string) $product->type)
        ) {
            $meter = preg_replace('/[\s\-.]/', '', $customerId);

            if (! preg_match('/^[0-9]{11}$/', $meter)) {
                throw ValidationException::withMessages([
                    'customer_id' => [
                        'Nomor meter PLN harus 11 digit angka.',
                    ],
                ]);
            }

            return $meter;
        }

        if (! $isPhoneProduct) {
            if (mb_strlen($customerId) > 50) {
                throw ValidationException::withMessages([
                    'customer_id' => ['Nomor tujuan terlalu panjang.'],
                ]);
            }

            return $customerId;
        }

        $digits = preg_replace('/[\s\-.]/', '', $customerId);

        if (str_starts_with($digits, '+62')) {
            $digits = '0' . substr($digits, 3);
        } elseif (str_starts_with($digits, '62')) {
            $digits = '0' . substr($digits, 2);
        }

        if (! preg_match('/^08[0-9]{8,11}$/', $digits)) {
            throw ValidationException::withMessages([
                'customer_id' => [
                    'Nomor HP tidak valid (harus diawali 08, 10-13 digit).',
                ],
            ]);
        }

        return $digits;
    }

    /**
     * ================================================================
     * PRICE GUARD
     * ================================================================
     *
     * Tolak transaksi kalau harga jual di bawah harga modal.
     */
    protected function assertPriceAboveCost(
        int $basePrice,
        int $sellPrice
    ): void {
        if ($sellPrice < $basePrice) {
            Log::warning('Harga jual di bawah modal', [
                'base_price' => $basePrice,
                'sell_price' => $sellPrice,
            ]);

            throw ValidationException::withMessages([
                'product' => [
                    'Harga produk sedang tidak valid, hubungi admin.',
                ],
            ]);
        }
    }

    /**
     * ================================================================
     * PRODUCT CODE
     * ================================================================
     */
    protected function productCode(
        Transactions $transaction
    ): string {
        $product = $transaction->product;

        if (! $product) {
            throw ValidationException::withMessages([
                'product' => [
                    'Produk transaksi tidak ditemukan.',
                ],
            ]);
        }

        $code =
            $product->product_code
            ?? $product->code
            ?? $product->sku
            ?? null;

        if (! $code) {
            throw ValidationException::withMessages([
                'product' => [
                    'Kode produk IAK tidak ditemukan.',
                ],
            ]);
        }

        return (string) $code;
    }

    /**
     * ================================================================
     * NORMALIZE IAK STATUS
     * ================================================================
     */
    protected function normalizeIakStatus(
        array $response
    ): string {
        $data = $response['data'] ?? $response;

        $status = trim((string) ($data['status'] ?? ''));

        $rc = strtolower(
            trim(
                (string) (
                    $data['rc']
                    ?? $data['response_code']
                    ?? ''
                )
            )
        );

        /*
     * Sesuai dokumentasi resmi IAK:
     * status: 0 = PROCESS, 1 = SUCCESS, 2 = FAILED
     * rc: "00" = SUCCESS
     */
        if ($status === '1' || $rc === '00') {
            return 'success';
        }

        if ($status === '2') {
            return 'failed';
        }

        /*
     * Fallback untuk kemungkinan format string
     * (jaga-jaga kalau ada endpoint IAK lain yang beda format).
     */
        $statusLower = strtolower($status);

        if (
            in_array(
                $statusLower,
                [
                    'success',
                    'successful',
                    'sukses',
                    'completed',
                    'complete',
                    'settlement',
                ],
                true
            )
        ) {
            return 'success';
        }

        if (
            in_array(
                $statusLower,
                [
                    'failed',
                    'failure',
                    'fail',
                    'gagal',
                    'error',
                    'cancelled',
                    'canceled',
                ],
                true
            )
        ) {
            return 'failed';
        }

        /*
     * status = 0 (PROCESS) atau tidak dikenali → tetap PENDING.
     * Lebih aman daripada salah commit/release.
     */
        return 'pending';
    }
    /**
     * ================================================================
     * EXTRACT SN
     * ================================================================
     */
    protected function extractSn(
        array $response
    ): ?string {
        $sn =
            $response['sn']
            ?? $response['data']['sn']
            ?? null;

        if (
            $sn === null
            || $sn === ''
        ) {
            return null;
        }

        return (string) $sn;
    }

    /**
     * ================================================================
     * EXTRACT FAILURE REASON
     * ================================================================
     */
    protected function extractFailureReason(
        array $response
    ): ?string {
        $reason =
            $response['message']
            ?? $response['msg']
            ?? $response['error']
            ?? $response['data']['message']
            ?? $response['data']['msg']
            ?? $response['data']['error']
            ?? null;

        if (
            $reason === null
            || $reason === ''
        ) {
            return null;
        }

        if (is_array($reason)) {
            return json_encode(
                $reason,
                JSON_UNESCAPED_UNICODE
            );
        }

        return (string) $reason;
    }
}
