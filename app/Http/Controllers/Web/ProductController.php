<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Services\Iak\ProductSyncService;
use Illuminate\Http\Request;
use RuntimeException;

class ProductController extends Controller
{
    /**
     * Katalog produk aktif untuk agen & user.
     * Harga otomatis mengikuti role yang login.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $type = $request->query('type');
        $brand = $request->query('brand');
        $isPln = $type && str_contains(strtolower($type), 'pln');

        // Pra/pasca bayar hanya relevan untuk PLN (kolom products.category).
        // Halaman PLN memakai tab Token Listrik / Tagihan Listrik, jadi default-nya pra bayar.
        $kind = $isPln
            ? (in_array($request->query('kind'), ['prepaid', 'postpaid'], true) ? $request->query('kind') : 'prepaid')
            : null;

        $active = fn () => Products::sellable();

        $products = $active()
            ->when($request->category, fn ($q, $v) => $q->where('category', $v))
            ->when($type, fn ($q, $v) => $q->where('type', $v))
            ->when($brand, fn ($q, $v) => $q->where('brand', $v))
            ->when($kind, fn ($q, $v) => $q->where('category', $v))
            ->when($request->q, function ($q, $v) {
                $q->where(function ($q) use ($v) {
                    $q->where('name', 'like', "%{$v}%")
                        ->orWhere('brand', 'like', "%{$v}%")
                        ->orWhere('product_code', 'like', "%{$v}%");
                });
            })
            ->orderBy('type')->orderBy('brand')->orderBy('base_price')
            ->paginate(24)
            ->withQueryString();

        // Chip provider / game: brand milik jenis yang sedang dipilih, lengkap dengan jumlah produk.
        $brands = $type
            ? $active()->where('type', $type)->whereNotNull('brand')
                ->when($kind, fn ($q, $v) => $q->where('category', $v))
                ->selectRaw('brand, COUNT(*) as total')
                ->groupBy('brand')->orderBy('brand')->get()
            : collect();

        $kindCounts = $isPln
            ? $active()->where('type', $type)
                ->when($brand, fn ($q, $v) => $q->where('brand', $v))
                ->selectRaw('category, COUNT(*) as total')
                ->groupBy('category')->pluck('total', 'category')
            : collect();

        $t = strtolower((string) $type);

        return view('products.index', [
            'products' => $products,
            'role' => $user->role,
            'types' => $active()->whereNotNull('type')->distinct()->orderBy('type')->pluck('type'),
            'brands' => $brands,
            'kind' => $kind,
            'kindCounts' => $kindCounts,
            'isPln' => $isPln,
            // Pulsa / paket data: ada kolom nomor HP untuk deteksi operator otomatis.
            'showNumber' => $t !== '' && (str_contains($t, 'pulsa') || str_contains($t, 'data')),
            // Kategori yang ditampilkan sebagai grid kartu, bukan daftar memanjang.
            'gridMode' => $t !== '' && (bool) preg_match('/pln|voucher|game|esim/', $t),
            'brandLabel' => str_contains($t, 'game') ? 'Game' : 'Provider',
        ]);
    }

    /**
     * Hanya superadmin: sync price list dari IAK API ke tabel products.
     */
    public function sync(ProductSyncService $productSyncService)
    {
        try {
            $result = $productSyncService->sync();
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with(
            'success',
            "Sync selesai. Produk baru: {$result['created']}, produk diupdate: {$result['updated']}."
        );
    }
}
