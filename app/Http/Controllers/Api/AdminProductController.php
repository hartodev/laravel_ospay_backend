<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminProductController extends Controller
{
    /**
     * List produk untuk admin.
     *
     * ?needs_pricing=1  -> hanya produk yang price_user/price_agent
     *                      masih sama dengan base_price (belum
     *                      pernah di-review admin, margin masih 0)
     * ?search=...       -> cari di name/product_code/brand
     * ?category=...     -> filter category (prepaid/postpaid)
     * ?status=...       -> filter status (active/inactive)
     */
    public function index(Request $request)
    {
        $products = Products::query()
            ->when($request->boolean('needs_pricing'), function ($q) {
                $q->whereColumn('price_user', 'base_price')
                    ->whereColumn('price_agent', 'base_price');
            })
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                        ->orWhere('product_code', 'like', "%{$request->search}%")
                        ->orWhere('brand', 'like', "%{$request->search}%");
                });
            })
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(30);

        return response()->json($products);
    }

    /**
     * Update harga jual (price_user/price_agent) dan/atau status.
     * base_price TIDAK bisa diubah manual di sini - itu murni
     * hasil sync dari IAK.
     */
    public function update(Request $request, Products $product)
    {
        $data = $request->validate([
            'price_user' => ['sometimes', 'integer', 'min:0'],
            'price_agent' => ['sometimes', 'integer', 'min:0'],
            'price_guest' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);

        if (empty($data)) {
            throw ValidationException::withMessages([
                'data' => ['Tidak ada field yang diupdate.'],
            ]);
        }

        /*
         * Tidak diblokir kalau harga jual di bawah base_price (cost)
         * - admin mungkin sengaja bikin promo rugi sementara.
         * Tapi WAJIB dikasih warning di response supaya tidak
         * ke-set tanpa sadar.
         */
        $warnings = [];

        if (isset($data['price_user']) && $data['price_user'] < $product->base_price) {
            $warnings[] = "price_user ({$data['price_user']}) di bawah base_price ({$product->base_price}) - margin akan negatif.";
        }

        if (isset($data['price_agent']) && $data['price_agent'] < $product->base_price) {
            $warnings[] = "price_agent ({$data['price_agent']}) di bawah base_price ({$product->base_price}) - margin akan negatif.";
        }

        $product->update($data);

        return response()->json([
            'message' => 'Produk berhasil diupdate.',
            'product' => $product->fresh(),
            'warnings' => $warnings,
        ]);
    }
}
