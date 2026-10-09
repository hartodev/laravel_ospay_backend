<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AgentController extends Controller
{
    // List user yang dinaungi agen yang login.
    // (User TIDAK punya wallet, jadi tidak ada with('wallet') seperti versi API.)
    public function subUsers(Request $request)
    {
        return view('agen.sub-users.index', [
            'subUsers' => $request->user()->subUsers()->latest()->paginate(15),
        ]);
    }

    public function createSubUser()
    {
        return view('agen.sub-users.create');
    }

    // Agen mendaftarkan user baru di bawah naungannya.
    public function storeSubUser(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'role' => 'user',
            'status' => 'active',
            'parent_agent_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('agen.sub-users.index')
            ->with('success', 'Sub-user berhasil didaftarkan.');
    }

    // Riwayat komisi + ringkasan pending vs paid.
    public function commissions(Request $request)
    {
        $agent = $request->user();

        return view('agen.commissions', [
            'commissions' => $agent->commissions()->with('transaction.product')->latest()->paginate(15),
            'totalPending' => (int) $agent->commissions()->where('status', 'pending')->sum('amount'),
            'totalPaid' => (int) $agent->commissions()->where('status', 'paid')->sum('amount'),
        ]);
    }
}
