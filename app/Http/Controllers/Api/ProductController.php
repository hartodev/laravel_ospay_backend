<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Services\Iak\ProductSyncService;
use Illuminate\Http\Request;
use RuntimeException;

class ProductController extends Controller
{
    // List produk aktif, harga otomatis mengikuti role user yang login
    public function index(Request $request)
    {
        $user = $request->user();

        $products = Products::sellable()
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->get()
            ->map(function (Products $product) use ($user) {
                return [
                    'id' => $product->id,
                    'product_code' => $product->product_code,
                    'brand' => $product->brand,
                    'type' => $product->type,
                    'name' => $product->name,
                    'price' => $product->priceForRole($user->role),
                ];
            });

        return response()->json($products);
    }

    // Hanya superadmin: sync price list dari IAK ke tabel products lokal
    public function sync(Request $request, ProductSyncService $productSyncService)
    {
        try {
            $result = $productSyncService->sync();
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        return response()->json([
            'message' => "Sync selesai. Produk baru: {$result['created']}, produk diupdate: {$result['updated']}.",
        ]);
    }
}
