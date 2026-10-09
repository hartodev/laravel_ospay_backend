<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Services\Fcm\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceTokenController extends Controller
{
    /**
     * POST /api/device-token
     * Simpan / pindahkan token FCM ke akun yang sedang login.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:512'],
            'platform' => ['nullable', 'string', 'in:android,ios'],
            'notify_login' => ['nullable', 'boolean'],
        ]);

        // Token unik per HP: kalau HP dipakai akun lain, token berpindah pemilik.
        DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $data['platform'] ?? null,
                'last_used_at' => now(),
            ]
        );

        Log::info('DeviceToken: token tersimpan', [
            'user_id' => $request->user()->id,
            'notify_login' => $request->boolean('notify_login'),
        ]);

        // Login baru: kirim push "Login Berhasil" ke perangkat ini.
        // Dikirim di sini (bukan di AuthController) karena token perangkat
        // baru terdaftar setelah login selesai.
        if ($request->boolean('notify_login')) {
            $user = $request->user();

            $sent = app(FcmService::class)->sendToUser(
                $user->id,
                'Login Berhasil',
                'Selamat datang, ' . $user->name . '!',
                ['type' => 'login_success']
            );

            Log::info('DeviceToken: push login dikirim', [
                'user_id' => $user->id,
                'terkirim' => $sent,
            ]);
        }

        return response()->json(['message' => 'Token perangkat tersimpan.']);
    }

    /**
     * DELETE /api/device-token
     * Dipanggil saat logout supaya HP ini berhenti menerima notifikasi akun tsb.
     */
    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:512'],
        ]);

        DeviceToken::where('token', $data['token'])
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['message' => 'Token perangkat dihapus.']);
    }
}
