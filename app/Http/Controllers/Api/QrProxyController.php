<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Meneruskan gambar QRIS dari Midtrans.
 *
 * Dipakai Flutter Web: browser memblokir gambar langsung dari Midtrans
 * karena server mereka tidak mengirim header CORS. Hanya URL QRIS Midtrans
 * yang diizinkan (mencegah dipakai sebagai proxy bebas / SSRF).
 *
 * GET /api/qr-proxy?url=https://api.sandbox.midtrans.com/v2/qris/{id}/qr-code
 */
class QrProxyController extends Controller
{
    private const ALLOWED_HOSTS = [
        'api.sandbox.midtrans.com',
        'api.midtrans.com',
    ];

    public function __invoke(Request $request)
    {
        $url = (string) $request->query('url', '');
        $parts = parse_url($url);

        abort_unless(
            is_array($parts)
                && ($parts['scheme'] ?? '') === 'https'
                && in_array($parts['host'] ?? '', self::ALLOWED_HOSTS, true)
                && str_starts_with($parts['path'] ?? '', '/v2/qris/'),
            422,
            'URL QR tidak valid.'
        );

        $res = Http::timeout(15)->get($url);

        abort_unless($res->successful(), 502, 'Gagal mengambil gambar QR.');

        return response($res->body(), 200, [
            'Content-Type' => $res->header('Content-Type') ?: 'image/png',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
