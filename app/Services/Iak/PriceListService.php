<?php

namespace App\Services\Iak;

class PriceListService extends Iak
{
    public function getAll(string $status = 'all'): array
    {
        $response = $this->http()->post($this->prepaidUrl() . '/api/pricelist', [
            'status' => $status,
            'username' => $this->username,
            'sign' => $this->sign('pl'), // price list: additional = 'pl'
        ]);

        return $response->json();
    }
}
