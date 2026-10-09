<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAgentController extends Controller
{
    /**
     * ================================================================
     * LIST SEMUA AGEN
     * ================================================================
     */
    public function index(
        Request $request
    ) {
        $agents = User::query()
            ->where('role', 'agen')

            ->when(
                $request->status,
                fn($query, $status) =>
                $query->where(
                    'status',
                    $status
                )
            )

            ->latest()

            ->paginate(15);

        return response()->json(
            $agents
        );
    }

    /**
     * ================================================================
     * LIST AGEN PENDING
     * ================================================================
     */
    public function pending()
    {
        $agents = User::query()
            ->where('role', 'agen')
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);

        return response()->json(
            $agents
        );
    }

    /**
     * ================================================================
     * APPROVE AGEN
     * ================================================================
     *
     * pending
     *    ↓
     * active
     *    ↓
     * wallet dibuat
     */
    public function approve(
        User $user
    ) {
        abort_if(
            ! $user->isAgent(),
            404
        );

        if (! $user->isPending()) {
            return response()->json([
                'message' =>
                'Agen bukan dalam status pending.',
            ], 422);
        }

        DB::transaction(function () use (
            $user
        ) {
            /*
             * Aktifkan agen.
             */
            $user->update([
                'status' => 'active',
            ]);

            /*
             * Buat wallet kalau belum ada.
             *
             * firstOrCreate membuat endpoint
             * aman jika approve terpanggil ulang.
             */
            Wallets::firstOrCreate(
                [
                    'user_id' => $user->id,
                ],
                [
                    'balance' => 0,
                    'held_balance' => 0,
                ]
            );
        });

        return response()->json([
            'message' =>
            'Agen berhasil disetujui.',

            'user' =>
            $user
                ->fresh()
                ->load('wallet'),
        ]);
    }

    /**
     * ================================================================
     * REJECT AGEN
     * ================================================================
     */
    public function reject(
        User $user
    ) {
        abort_if(
            ! $user->isAgent(),
            404
        );

        if (! $user->isPending()) {
            return response()->json([
                'message' =>
                'Agen bukan dalam status pending.',
            ], 422);
        }

        $user->update([
            'status' => 'suspended',
        ]);

        return response()->json([
            'message' =>
            'Pendaftaran agen ditolak.',

            'user' =>
            $user->fresh(),
        ]);
    }

    /**
     * ================================================================
     * SUSPEND AGEN
     * ================================================================
     */
    public function suspend(
        User $user
    ) {
        abort_if(
            ! $user->isAgent(),
            404
        );

        if ($user->isSuspended()) {
            return response()->json([
                'message' =>
                'Agen sudah dalam status suspended.',
            ]);
        }

        $user->update([
            'status' => 'suspended',
        ]);

        /*
         * Wallet TIDAK dihapus.
         *
         * Histori saldo dan transaksi harus tetap ada.
         */
        return response()->json([
            'message' =>
            'Agen berhasil dinonaktifkan.',

            'user' =>
            $user->fresh()
                ->load('wallet'),
        ]);
    }

    /**
     * ================================================================
     * ACTIVATE AGEN KEMBALI
     * ================================================================
     *
     * Kita tambahkan endpoint ini karena suspend
     * seharusnya bisa dibatalkan oleh superadmin.
     */
    public function activate(
        User $user
    ) {
        abort_if(
            ! $user->isAgent(),
            404
        );

        if ($user->isActive()) {
            return response()->json([
                'message' =>
                'Agen sudah aktif.',
            ]);
        }

        DB::transaction(function () use (
            $user
        ) {
            $user->update([
                'status' => 'active',
            ]);

            Wallets::firstOrCreate(
                [
                    'user_id' => $user->id,
                ],
                [
                    'balance' => 0,
                    'held_balance' => 0,
                ]
            );
        });

        return response()->json([
            'message' =>
            'Agen berhasil diaktifkan kembali.',

            'user' =>
            $user
                ->fresh()
                ->load('wallet'),
        ]);
    }
}
