<?php

namespace App\Services\Iak;

class InquiryPlnService extends Iak
{
    public function inquiry(string $customerId): array
    {
        $response = $this->http()->post($this->prepaidUrl() . '/api/inquiry-pln', [
            'customer_id' => $customerId,
            'username' => $this->username,
            'sign' => $this->sign($customerId), // inquiry PLN: additional = customer_id
        ]);

        return $response->json();
    }
}
