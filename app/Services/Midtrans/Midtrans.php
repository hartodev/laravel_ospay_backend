<?php

namespace App\Services\Midtrans;

use Midtrans\Config;

class Midtrans
{
    protected string $serverKey;

    protected bool $isProduction;

    protected bool $isSanitized;

    protected bool $is3ds;

    public function __construct()
    {
        $this->serverKey =
            config('midtrans.server_key');

        $this->isProduction =
            (bool) config('midtrans.is_production');

        $this->isSanitized =
            (bool) config('midtrans.is_sanitized');

        $this->is3ds =
            (bool) config('midtrans.is_3ds');

        $this->configureMidtrans();
    }

    protected function configureMidtrans(): void
    {
        Config::$serverKey =
            $this->serverKey;

        Config::$isProduction =
            $this->isProduction;

        Config::$isSanitized =
            $this->isSanitized;

        Config::$is3ds =
            $this->is3ds;
    }
}
