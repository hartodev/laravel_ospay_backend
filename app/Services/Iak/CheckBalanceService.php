<?php

namespace App\Services\Iak;

class CheckBalanceService extends Iak
{
    public function check(): array
    {
        $response = $this->http()->post($this->prepaidUrl() . '/api/check-balance', [
            'username' => $this->username,
            'sign' => $this->sign('bl'), // check balance: additional = 'bl'
        ]);

        return $response->json();
    }
}
