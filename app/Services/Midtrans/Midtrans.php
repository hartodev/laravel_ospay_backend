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

        $this->configureNotificationUrl();
    }

    /**
     * Override Notification URL per transaksi (header X-Override-Notification).
     *
     * Dipakai kalau satu akun Midtrans dipakai beberapa aplikasi:
     * notifikasi transaksi OSPay hanya dikirim ke URL OSPay, dan URL
     * di dashboard (milik aplikasi lain) tidak ikut menerima.
     * QRIS GoPay juga hanya membaca URL notifikasi dari request API.
     *
     * Aktif hanya jika MIDTRANS_NOTIFICATION_URL diisi di .env
     * (kosongkan di lokal/dev supaya memakai URL di dashboard sandbox).
     */
    protected function configureNotificationUrl(): void
    {
        $url = trim((string) config('midtrans.notification_url'));

        if ($url === '') {
            return;
        }

        if (! property_exists(Config::class, 'overrideNotifUrl')) {
            \Illuminate\Support\Facades\Log::warning(
                'midtrans-php belum mendukung overrideNotifUrl; jalankan composer update midtrans/midtrans-php.'
            );

            return;
        }

        Config::$overrideNotifUrl = $url;
    }
}
