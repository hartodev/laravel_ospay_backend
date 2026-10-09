<?php

namespace App\Services\Midtrans;

use App\Models\Deposits;
use App\Models\Transactions;
use Illuminate\Http\Request;

class CallbackService extends Midtrans
{
    protected array $payload;

    public function __construct(?Request $request = null)
    {
        parent::__construct();

        $this->payload = $request
            ? $request->all()
            : request()->all();
    }

    /**
     * Payload callback Midtrans.
     */
    public function payload(): array
    {
        return $this->payload;
    }

    /**
     * Order ID dari Midtrans.
     */
    public function orderId(): ?string
    {
        return $this->payload['order_id'] ?? null;
    }

    /**
     * Status transaksi Midtrans.
     */
    public function transactionStatus(): ?string
    {
        return $this->payload['transaction_status'] ?? null;
    }

    /**
     * Fraud status.
     */
    public function fraudStatus(): ?string
    {
        return $this->payload['fraud_status'] ?? null;
    }

    /**
     * Payment type.
     */
    public function paymentType(): ?string
    {
        return $this->payload['payment_type'] ?? null;
    }

    /**
     * Signature key dari Midtrans.
     */
    public function signatureKey(): ?string
    {
        return $this->payload['signature_key'] ?? null;
    }

    /**
     * Validasi signature callback Midtrans.
     *
     * SHA512:
     *
     * order_id
     * + status_code
     * + gross_amount
     * + server_key
     */
    public function isSignatureKeyVerified(): bool
    {
        $orderId = $this->payload['order_id'] ?? null;
        $statusCode = $this->payload['status_code'] ?? null;
        $grossAmount = $this->payload['gross_amount'] ?? null;
        $signature = $this->payload['signature_key'] ?? null;

        if (
            ! $orderId ||
            ! $statusCode ||
            $grossAmount === null ||
            ! $signature
        ) {
            return false;
        }

        $serverKey = config('midtrans.server_key');

        $expectedSignature = hash(
            'sha512',
            $orderId
                . $statusCode
                . $grossAmount
                . $serverKey
        );

        return hash_equals(
            $expectedSignature,
            $signature
        );
    }

    /**
     * Apakah pembayaran berhasil.
     *
     * QRIS Midtrans:
     * settlement = sukses.
     *
     * Capture juga dianggap sukses selama
     * fraud_status bukan deny.
     */
    public function isSuccess(): bool
    {
        $status = $this->transactionStatus();

        if ($status === 'settlement') {
            return true;
        }

        if ($status === 'capture') {
            return $this->fraudStatus() !== 'deny';
        }

        return false;
    }

    /**
     * Apakah pembayaran expired.
     */
    public function isExpire(): bool
    {
        return $this->transactionStatus() === 'expire';
    }

    /**
     * Apakah pembayaran dibatalkan / ditolak.
     */
    public function isCancelled(): bool
    {
        return in_array(
            $this->transactionStatus(),
            [
                'cancel',
                'deny',
            ],
            true
        );
    }

    /**
     * Cari deposit berdasarkan order ID Midtrans.
     */
    public function getDeposit(): ?Deposits
    {
        $orderId = $this->orderId();

        if (! $orderId) {
            return null;
        }

        return Deposits::where(
            'transaction_number',
            $orderId
        )->first();
    }

    /**
     * Cari transaksi produk berdasarkan
     * Midtrans order ID.
     */
    public function getTransaction(): ?Transactions
    {
        $orderId = $this->orderId();

        if (! $orderId) {
            return null;
        }

        return Transactions::where(
            'midtrans_order_id',
            $orderId
        )->first();
    }
}
