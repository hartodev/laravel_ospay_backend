<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AgentController extends Controller
{
    // List user yang dinaungi agen yang login
    public function subUsers(Request $request)
    {
        $subUsers = $request->user()->subUsers()
            ->with('wallet')
            ->latest()
            ->paginate(15);

        return response()->json($subUsers);
    }

    // Agen daftarin user baru di bawah naungannya sendiri
    public function storeSubUser(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $agent = $request->user();

        $user = DB::transaction(function () use ($data, $agent) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'role' => 'user',
                'status' => 'active',
                'parent_agent_id' => $agent->id,
            ]);

            Wallets::create(['user_id' => $user->id, 'balance' => 0]);

            return $user;
        });

        return response()->json([
            'message' => 'Sub-user berhasil didaftarkan.',
            'user' => $user,
        ], 201);
    }

    // Riwayat komisi agen, sekaligus ringkasan total pending vs paid
    public function commissions(Request $request)
    {
        $agent = $request->user();

        $commissions = $agent->commissions()
            ->with(['transaction.product'])
            ->latest()
            ->paginate(15);

        $summary = [
            'total_pending' => (int) $agent->commissions()->where('status', 'pending')->sum('amount'),
            'total_paid' => (int) $agent->commissions()->where('status', 'paid')->sum('amount'),
        ];

        return response()->json([
            'summary' => $summary,
            'commissions' => $commissions,
        ]);
    }
}
