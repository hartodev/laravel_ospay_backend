<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CommissionRule;
use App\Models\Commissions;
use App\Models\Products;
use App\Models\Transactions;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Superadmin: atur aturan komisi agen dan hitung komisi dari
 * transaksi nyata yang sudah sukses.
 */
class AdminCommissionController extends Controller
{
    public function index()
    {
        // Transaksi sub-user (QRIS) yang sudah sukses tapi belum punya komisi.
        $withoutCommission = Transactions::query()
            ->where('status', 'success')
            ->where('payment_method', 'midtrans')
            ->whereHas('user', fn ($q) => $q->whereNotNull('parent_agent_id'))
            ->whereNotIn('id', Commissions::select('transaction_id'))
            ->count();

        return view('admin.commissions.index', [
            'rules' => CommissionRule::with('product')
                ->orderBy('product_id')
                ->orderByDesc('id')
                ->paginate(30),
            'withoutCommission' => $withoutCommission,
            'noMarginProducts' => Products::where('status', 'active')
                ->whereColumn('price_user', '<=', 'base_price')
                ->count(),
            'totalCommissions' => Commissions::count(),
            'totalAmount' => (int) Commissions::sum('amount'),
            'pendingCommissions' => Commissions::with(['agent', 'transaction.product'])
                ->where('status', 'pending')->latest()->limit(50)->get(),
            'pendingTotal' => (int) Commissions::where('status', 'pending')->sum('amount'),
        ]);
    }

    /**
     * Satu aturan per produk (atau satu aturan umum kalau product_code
     * dikosongkan). Menyimpan ulang produk yang sama akan mengganti aturannya.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_code' => ['nullable', 'exists:products,product_code'],
            'type' => ['required', 'in:percentage,nominal'],
            'value' => ['required', 'numeric', 'gt:0'],
        ]);

        $this->assertValueForType($data['type'], (float) $data['value']);

        $productId = filled($data['product_code'] ?? null)
            ? Products::where('product_code', $data['product_code'])->value('id')
            : null;

        CommissionRule::updateOrCreate(
            ['product_id' => $productId, 'role' => 'agen'],
            [
                'type' => $data['type'],
                'value' => $data['value'],
                'status' => 'active',
            ]
        );

        return back()->with('success', 'Aturan komisi disimpan.');
    }

    public function update(Request $request, CommissionRule $rule)
    {
        $data = $request->validate([
            'type' => ['required', 'in:percentage,nominal'],
            'value' => ['required', 'numeric', 'gt:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->assertValueForType($data['type'], (float) $data['value']);

        $rule->update($data);

        return back()->with('success', 'Aturan komisi diperbarui.');
    }

    public function destroy(CommissionRule $rule)
    {
        $rule->delete();

        return back()->with('success', 'Aturan komisi dihapus.');
    }

    /**
     * Hitung komisi dari transaksi sub-user yang sudah sukses tetapi
     * belum punya komisi (mis. karena aturan baru dibuat belakangan).
     * Idempotent: transaksi yang sudah punya komisi tidak disentuh.
     */
    public function backfill(CommissionService $commissionService)
    {
        $r = $commissionService->backfill();

        return back()->with(
            'success',
            "Selesai. Komisi dibuat: {$r['created']}, dilewati: {$r['skipped']} "
            . '(tidak ada aturan aktif atau tidak ada margin).'
        );
    }

    /** Cairkan satu komisi ke saldo wallet agen. */
    public function payout(Commissions $commission, CommissionService $commissionService)
    {
        $commissionService->payout($commission);

        return back()->with('success', 'Komisi dicairkan ke saldo wallet agen.');
    }

    /** Cairkan semua komisi pending. */
    public function payoutAll(CommissionService $commissionService)
    {
        $r = $commissionService->payoutAllPending();

        return back()->with(
            'success',
            "Pencairan selesai. Berhasil: {$r['paid']}, gagal: {$r['failed']}."
        );
    }

    private function assertValueForType(string $type, float $value): void
    {
        if ($type === 'percentage' && $value > 100) {
            throw ValidationException::withMessages([
                'value' => ['Persentase tidak boleh lebih dari 100.'],
            ]);
        }
    }
}
