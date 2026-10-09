<?php

namespace App\Services\Fcm;

use App\Models\DeviceToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Kirim push notification lewat FCM HTTP v1 tanpa paket tambahan.
 *
 * Autentikasi: JWT (RS256) ditandatangani dengan private key service account
 * lalu ditukar jadi access token OAuth2 (di-cache ~55 menit).
 *
 * Tidak melempar exception ke pemanggil - kegagalan push tidak boleh
 * menggagalkan transaksi.
 */
class FcmService
{
    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    /**
     * Kirim ke semua perangkat milik satu user.
     *
     * @param  array<string,mixed>  $data  semua nilai diubah ke string
     */
    public function sendToUser(
        int $userId,
        string $title,
        string $body,
        array $data = []
    ): int {
        if (! config('fcm.enabled')) {
            return 0;
        }

        $tokens = DeviceToken::where('user_id', $userId)->pluck('token');

        if ($tokens->isEmpty()) {
            return 0;
        }

        try {
            $credentials = $this->credentials();
            $accessToken = $this->accessToken($credentials);
            $projectId = config('fcm.project_id') ?: ($credentials['project_id'] ?? null);

            if (! $projectId) {
                throw new RuntimeException('project_id Firebase tidak ditemukan.');
            }
        } catch (Throwable $e) {
            Log::error('FCM: konfigurasi tidak valid', ['error' => $e->getMessage()]);

            return 0;
        }

        $sent = 0;

        foreach ($tokens as $token) {
            if ($this->sendOne($projectId, $accessToken, $token, $title, $body, $data)) {
                $sent++;
            }
        }

        return $sent;
    }

    private function sendOne(
        string $projectId,
        string $accessToken,
        string $token,
        string $title,
        string $body,
        array $data
    ): bool {
        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                // FCM mewajibkan semua nilai data bertipe string.
                'data' => array_map(
                    fn ($v) => (string) ($v ?? ''),
                    $data
                ),
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'channel_id' => config('fcm.android_channel_id'),
                        'sound' => 'default',
                    ],
                ],
                'apns' => [
                    'payload' => ['aps' => ['sound' => 'default']],
                ],
            ],
        ];

        try {
            $response = Http::withToken($accessToken)
                ->timeout(10)
                ->post(
                    "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                    $payload
                );
        } catch (Throwable $e) {
            Log::warning('FCM: gagal menghubungi server', ['error' => $e->getMessage()]);

            return false;
        }

        if ($response->successful()) {
            Log::info('FCM: diterima server FCM', [
                'message_id' => $response->json('name'),
            ]);

            return true;
        }

        $errorCode = $response->json('error.details.0.errorCode')
            ?? $response->json('error.status');

        // Token sudah tidak berlaku (app dihapus / token diganti) -> bersihkan.
        if (in_array($errorCode, ['UNREGISTERED', 'NOT_FOUND'], true)) {
            DeviceToken::where('token', $token)->delete();
        }

        Log::warning('FCM: pengiriman ditolak', [
            'status' => $response->status(),
            'error' => $errorCode,
            'body' => mb_substr($response->body(), 0, 300),
        ]);

        return false;
    }

    // ------------------------------------------------------------------
    // OAuth2 service account
    // ------------------------------------------------------------------

    private function credentials(): array
    {
        $path = config('fcm.credentials');

        if (! $path || ! is_file($path)) {
            throw new RuntimeException("File service account Firebase tidak ada: {$path}");
        }

        $json = json_decode((string) file_get_contents($path), true);

        if (! is_array($json) || empty($json['private_key']) || empty($json['client_email'])) {
            throw new RuntimeException('File service account Firebase tidak valid.');
        }

        return $json;
    }

    private function accessToken(array $credentials): string
    {
        return Cache::remember(
            'fcm_access_token:' . md5($credentials['client_email']),
            now()->addMinutes(55),
            function () use ($credentials) {
                $now = time();
                $tokenUri = $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token';

                $jwt = $this->signJwt([
                    'iss' => $credentials['client_email'],
                    'scope' => self::SCOPE,
                    'aud' => $tokenUri,
                    'iat' => $now,
                    'exp' => $now + 3600,
                ], $credentials['private_key']);

                $response = Http::asForm()->timeout(10)->post($tokenUri, [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]);

                if (! $response->successful() || ! $response->json('access_token')) {
                    throw new RuntimeException(
                        'Gagal mengambil access token Google: ' . $response->body()
                    );
                }

                return $response->json('access_token');
            }
        );
    }

    private function signJwt(array $claims, string $privateKey): string
    {
        $b64 = fn (string $s) => rtrim(strtr(base64_encode($s), '+/', '-_'), '=');

        $unsigned = $b64(json_encode(['alg' => 'RS256', 'typ' => 'JWT']))
            . '.' . $b64(json_encode($claims));

        if (! openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Gagal menandatangani JWT service account.');
        }

        return $unsigned . '.' . $b64($signature);
    }
}
