<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAgentController extends Controller
{
    // Semua agen (?status=pending|active|suspended)
    public function index(Request $request)
    {
        $agents = User::query()
            ->where('role', 'agen')
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->with('wallet')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.agents.index', [
            'agents' => $agents,
            'status' => $request->status,
            'pendingCount' => User::where('role', 'agen')->where('status', 'pending')->count(),
        ]);
    }

    // pending -> active + wallet dibuat
    public function approve(User $user)
    {
        abort_unless($user->isAgent(), 404);

        if (! $user->isPending()) {
            return back()->with('error', 'Agen bukan dalam status pending.');
        }

        DB::transaction(function () use ($user) {
            $user->update(['status' => 'active']);

            // firstOrCreate: aman kalau approve terpanggil ulang.
            Wallets::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'held_balance' => 0]
            );
        });

        return back()->with('success', "Agen {$user->name} disetujui. Toko aktif di {$user->storeHost()}.");
    }

    public function reject(User $user)
    {
        abort_unless($user->isAgent(), 404);

        if (! $user->isPending()) {
            return back()->with('error', 'Agen bukan dalam status pending.');
        }

        $user->update(['status' => 'suspended']);

        return back()->with('success', "Pendaftaran {$user->name} ditolak.");
    }

    // Wallet TIDAK dihapus: histori saldo & transaksi harus tetap ada.
    public function suspend(User $user)
    {
        abort_unless($user->isAgent(), 404);

        if ($user->isSuspended()) {
            return back()->with('error', 'Agen sudah dalam status suspended.');
        }

        $user->update(['status' => 'suspended']);

        return back()->with('success', "Agen {$user->name} dinonaktifkan.");
    }

    public function activate(User $user)
    {
        abort_unless($user->isAgent(), 404);

        if ($user->isActive()) {
            return back()->with('error', 'Agen sudah aktif.');
        }

        DB::transaction(function () use ($user) {
            $user->update(['status' => 'active']);

            Wallets::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'held_balance' => 0]
            );
        });

        return back()->with('success', "Agen {$user->name} diaktifkan kembali.");
    }
}
