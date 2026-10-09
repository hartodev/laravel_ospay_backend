<?php

namespace App\Services\Iak;

use Illuminate\Http\Client\RequestException;

class TopUpService extends Iak
{
    /**
     * ================================================================
     * TOP UP
     * ================================================================
     *
     * Kirim transaksi produk ke IAK.
     *
     * ref_id HARUS unik dan sama dengan transaksi internal.
     *
     * IAK:
     *
     * customer_id
     * product_code
     * ref_id
     * username
     * sign
     *
     * ================================================================
     */
    public function topUp(
        string $customerId,
        string $productCode,
        string $refId
    ): array {
        $customerId = trim($customerId);
        $productCode = trim($productCode);
        $refId = trim($refId);

        if ($customerId === '') {
            throw new \InvalidArgumentException(
                'Customer ID tidak boleh kosong.'
            );
        }

        if ($productCode === '') {
            throw new \InvalidArgumentException(
                'Product code tidak boleh kosong.'
            );
        }

        if ($refId === '') {
            throw new \InvalidArgumentException(
                'Ref ID tidak boleh kosong.'
            );
        }

        $response = $this->http()
            ->post(
                $this->prepaidUrl() . '/api/top-up',
                [
                    'customer_id' => $customerId,
                    'product_code' => $productCode,
                    'ref_id' => $refId,
                    'username' => $this->username,
                    'sign' => $this->sign($refId),
                ]
            );

        /*
         * Jangan diam-diam menganggap HTTP error
         * sebagai response IAK biasa.
         *
         * HTTP 4xx / 5xx akan masuk catch
         * di TransactionService.
         */
        $response->throw();

        $json = $response->json();

        if (! is_array($json)) {
            throw new \RuntimeException(
                'Response IAK tidak valid.'
            );
        }

        return $json;
    }
}
