<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Komisi milik agen yang sedang login. Butuh token (auth:sanctum).
 *
 * Memakai tabel commissions yang sudah ada (kolom agent_id, status
 * pending|paid). Status "paid" dikirim ke aplikasi sebagai "settled"
 * supaya cocok dengan model di Flutter.
 */
class CommissionController extends Controller
{
    // GET /api/commissions/summary
    public function summary(Request $request): JsonResponse
    {
        $this->authorizeViewer($request);
        $agentId = $request->user()->id;

        $all = Commissions::query()->where('agent_id', $agentId);

        return response()->json(['data' => [
            'total' => (int) (clone $all)->sum('amount'),
            'this_month' => (int) (clone $all)
                ->where('created_at', '>=', now()->startOfMonth())
                ->sum('amount'),
            'pending' => (int) (clone $all)->where('status', 'pending')->sum('amount'),
            'settled' => (int) (clone $all)->where('status', 'paid')->sum('amount'),
            'count' => (clone $all)->count(),
        ]]);
    }

    // GET /api/commissions?page=1   (format paginasi Laravel)
    public function index(Request $request): JsonResponse
    {
        $this->authorizeViewer($request);

        $paginator = Commissions::query()
            ->where('agent_id', $request->user()->id)
            ->with(['transaction.product:id,name'])
            ->latest()
            ->paginate(15)
            ->through(fn (Commissions $c) => [
                'id' => $c->id,
                'amount' => (int) $c->amount,
                'status' => $c->status === 'paid' ? 'settled' : $c->status,
                'description' => 'Komisi transaksi '.($c->transaction?->ref_id ?? $c->transaction_id),
                'ref_id' => $c->transaction?->ref_id,
                'product_name' => $c->transaction?->product?->name,
                'created_at' => $c->created_at?->toIso8601String(),
            ]);

        return response()->json($paginator);
    }

    private function authorizeViewer(Request $request): void
    {
        abort_unless(
            in_array($request->user()?->role, config('ppob.commission.view_roles', []), true),
            403,
            'Fitur komisi hanya untuk akun Agen.'
        );
    }
}
