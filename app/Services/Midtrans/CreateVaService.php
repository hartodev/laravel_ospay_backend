<?php

namespace App\Services\Midtrans;

use App\Models\Deposits;
use Midtrans\CoreApi;
use Midtrans\Transaction;

class CreateVaService extends Midtrans
{
    /**
     * Generate QRIS untuk deposit agen.
     */
    public function getQrisFor(
        Deposits $deposit
    ): array {
        $deposit->loadMissing('user');

        $params = [
            'payment_type' => 'qris',

            'transaction_details' => [
                'order_id' =>
                $deposit->transaction_number,

                'gross_amount' =>
                $deposit->amount,
            ],

            'item_details' => [[
                'id' => 'TOPUP_SALDO',

                'price' => $deposit->amount,

                'quantity' => 1,

                'name' => 'Top Up Saldo Agen',
            ]],

            'customer_details' => [
                'first_name' =>
                $deposit->user->name,

                'email' =>
                $deposit->user->email,
            ],

            'qris' => [
                'acquirer' => 'gopay',
            ],
        ];

        $response =
            CoreApi::charge($params);

        return json_decode(
            json_encode($response),
            true
        );
    }

    /**
     * Tanya status transaksi langsung ke Midtrans (Status API).
     * Dipakai sebagai cadangan kalau callback tidak sampai
     * (mis. saat development di localhost).
     */
    public function getStatus(string $orderId): array
    {
        $response = Transaction::status($orderId);

        return json_decode(
            json_encode($response),
            true
        );
    }

    /**
     * Ambil URL QRIS.
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
