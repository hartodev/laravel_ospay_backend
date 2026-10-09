<?php

namespace App\Services\Midtrans;

use App\Models\Transactions;
use Midtrans\CoreApi;
use Midtrans\Transaction as MidtransTransaction;

class CreateTransactionVaService extends Midtrans
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Generate QRIS untuk pembelian produk USER.
     *
     * Service ini hanya membuat pembayaran Midtrans.
     *
     * TIDAK memanggil IAK.
     */
    public function getQris(
        Transactions $transaction
    ): array {

        $transaction->loadMissing([
            'user',
            'product',
        ]);

        $params = [
            'payment_type' => 'qris',

            'transaction_details' => [
                'order_id' =>
                $transaction->midtrans_order_id,

                'gross_amount' =>
                $transaction->sell_price,
            ],

            'item_details' => [[
                'id' =>
                $transaction->product->product_code,

                'price' =>
                $transaction->sell_price,

                'quantity' => 1,

                'name' =>
                $transaction->product->name,
            ]],

            'customer_details' => [
                'first_name' =>
                $transaction->user->name,

                'email' =>
                $transaction->user->email,
            ],

            'qris' => [
                'acquirer' => 'gopay',
            ],
        ];

        $response = CoreApi::charge($params);

        return json_decode(
            json_encode($response),
            true
        );
    }

    /**
     * Cek status pembayaran langsung ke Midtrans.
     *
     * Cadangan untuk callback yang tidak sampai
     * (mis. development di localhost).
     */
    public function getStatus(string $orderId): array
    {
        $response = MidtransTransaction::status($orderId);

        return json_decode(
            json_encode($response),
            true
        );
    }

    /**
     * Ambil URL QRIS dari response Midtrans.
     */
    public static function extractQrUrl(
        array $response
    ): ?string {

        foreach (
            $response['actions'] ?? []
            as $action
        ) {

            if (
                ($action['name'] ?? '') ===
                'generate-qr-code'
            ) {
                return $action['url'] ?? null;
            }
        }

        return null;
    }
}
