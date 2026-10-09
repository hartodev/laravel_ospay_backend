<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\Transactions;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Satu URL /dashboard, isinya mengikuti role.
     */
    public function index(Request $request, WalletService $walletService)
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return view('dashboard.superadmin', [
                'pendingAgents' => User::where('role', 'agen')->where('status', 'pending')->count(),
                'activeAgents' => User::where('role', 'agen')->where('status', 'active')->count(),
                'activeProducts' => Products::where('status', 'active')->count(),
                'needsPricing' => Products::whereColumn('price_user', 'base_price')
                    ->whereColumn('price_agent', 'base_price')->count(),
                'trxToday' => Transactions::whereDate('created_at', today())->count(),
                'trxSuccessToday' => Transactions::whereDate('created_at', today())
                    ->where('status', 'success')->count(),
            ]);
        }

        if ($user->isAgent()) {
            return view('dashboard.agen', [
                'wallet' => $walletService->getForAgent($user),
                'pendingCommission' => (int) $user->commissions()->where('status', 'pending')->sum('amount'),
                'paidCommission' => (int) $user->commissions()->where('status', 'paid')->sum('amount'),
                'subUserCount' => $user->subUsers()->count(),
                'latest' => $user->transactions()->with('product')->latest()->limit(5)->get(),
            ]);
        }

        return view('dashboard.user', [
            'total' => $user->transactions()->count(),
            'success' => $user->transactions()->where('status', 'success')->count(),
            'latest' => $user->transactions()->with('product')->latest()->limit(5)->get(),
        ]);
    }
}
