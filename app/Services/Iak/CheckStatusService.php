<?php

namespace App\Services\Iak;

class CheckStatusService extends Iak
{
    /**
     * ================================================================
     * CHECK STATUS
     * ================================================================
     *
     * Mengecek transaksi yang sebelumnya sudah dikirim
     * ke IAK.
     *
     * ================================================================
     */
    public function check(
        string $refId
    ): array {
        $refId = trim($refId);

        if ($refId === '') {
            throw new \InvalidArgumentException(
                'Ref ID tidak boleh kosong.'
            );
        }

        $response = $this->http()
            ->post(
                $this->prepaidUrl() . '/api/check-status',
                [
                    'ref_id' => $refId,
                    'username' => $this->username,
                    'sign' => $this->sign($refId),
                ]
            );

        $response->throw();

        $json = $response->json();

        if (! is_array($json)) {
            throw new \RuntimeException(
                'Response check status IAK tidak valid.'
            );
        }

        return $json;
    }
}
