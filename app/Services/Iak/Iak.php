<?php

namespace App\Services\Iak;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

abstract class Iak
{
    protected string $username;

    protected string $apiKey;

    protected bool $isProduction;

    public function __construct()
    {
        $this->username = config('iak.username');

        $this->isProduction = (bool) config(
            'iak.is_production'
        );

        $this->apiKey = $this->isProduction
            ? config('iak.api_key.prod')
            : config('iak.api_key.dev');
    }

    /**
     * ================================================================
     * PREPAID URL
     * ================================================================
     */
    protected function prepaidUrl(): string
    {
        return $this->isProduction
            ? config('iak.prepaid_url.prod')
            : config('iak.prepaid_url.dev');
    }

    /**
     * ================================================================
     * POSTPAID URL
     * ================================================================
     */
    protected function postpaidUrl(): string
    {
        return $this->isProduction
            ? config('iak.postpaid_url.prod')
            : config('iak.postpaid_url.dev');
    }

    /**
     * ================================================================
     * SIGNATURE
     * ================================================================
     *
     * MD5(username + api_key + additional)
     *
     * Additional dapat berupa:
     *
     * - bl
     * - pl
     * - ref_id
     * - customer_id
     */
    protected function sign(string $additional): string
    {
        return md5(
            $this->username
                . $this->apiKey
                . $additional
        );
    }

    /**
     * ================================================================
     * HTTP CLIENT
     * ================================================================
     */
    protected function http(): PendingRequest
    {
        return Http::asJson()
            ->acceptJson()
            ->timeout(30);
    }
}
