<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Services\PricelistImportService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class AdminProductController extends Controller
{
    /**
     * ?needs_pricing=1 -> hanya produk yang price_user/price_agent masih
     *                     sama dengan base_price (belum di-review admin)
     * ?search=...      -> cari di name/product_code/brand
     * ?category=...    -> prepaid/postpaid
     * ?status=...      -> active/inactive
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
            ->when($request->category, fn ($q, $v) => $q->where('category', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.products.index', ['products' => $products]);
    }

    /**
     * Update harga jual dan/atau status.
     * base_price TIDAK bisa diubah manual - murni hasil sync/import.
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

        // Tidak diblokir kalau di bawah base_price (promo rugi disengaja),
        // tapi WAJIB ada peringatan supaya tidak ke-set tanpa sadar.
        $warnings = [];

        // Harga tamu kosong/0 = tidak dijual ke tamu.
        if (array_key_exists('price_guest', $data) && empty($data['price_guest'])) {
            $data['price_guest'] = null;
        }

        foreach (['price_user', 'price_agent', 'price_guest'] as $field) {
            if (! is_null($data[$field] ?? null) && $data[$field] < $product->base_price) {
                $warnings[] = "{$field} ({$data[$field]}) di bawah base_price ({$product->base_price}) - margin negatif.";
            }
        }

        $product->update($data);

        $redirect = back()->with('success', "Produk {$product->product_code} berhasil diupdate.");

        return $warnings ? $redirect->with('warning', implode(' ', $warnings)) : $redirect;
    }

    /**
     * Import pricelist dari file xlsx IAK.
     */
    public function import(Request $request, PricelistImportService $importService)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx', 'max:10240'],
        ]);

        try {
            $r = $importService->import($request->file('file')->getRealPath());
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }

        $msg = "Import selesai ({$r['sheets']} sheet). Produk baru: {$r['created']}, diupdate: {$r['updated']}.";

        $back = back()->with('success', $msg);

        return $r['skipped_sheets']
            ? $back->with('warning', 'Sheet dilewati: ' . implode(', ', $r['skipped_sheets']))
            : $back;
    }
}
