<?php

namespace App\Services\Iak;

use App\Models\Products;
use RuntimeException;

class ProductSyncService
{
    public function __construct(private PriceListService $priceListService) {}

    /**
     * Sinkronisasi base_price + status produk prepaid dari IAK.
     *
     * Produk existing: HANYA base_price, status, raw_response yang
     * diupdate. price_user/price_agent/brand/type/name/description
     * TIDAK disentuh - itu wilayah admin (lihat AdminProductController).
     *
     * Produk baru: insert lengkap, price_user = price_agent = base_price
     * (margin 0, WAJIB di-review admin sebelum dijual).
     *
     * @return array{created: int, updated: int}
     *
     * @throws RuntimeException kalau IAK me-return error (rc != '00')
     */
    public function sync(): array
    {
        $response = $this->priceListService->getAll();

        $rc = $response['data']['rc'] ?? null;

        if ($rc !== '00') {
            throw new RuntimeException(
                'Gagal ambil price list dari IAK: '
                    . ($response['data']['message'] ?? 'unknown error')
            );
        }

        $items = collect($response['data']['pricelist'] ?? [])
            ->filter(fn($item) => is_array($item) && isset($item['product_code']));

        $created = 0;
        $updated = 0;

        foreach ($items as $item) {
            $status = strtolower(trim($item['status'] ?? '')) === 'active'
                ? 'active'
                : 'inactive';

            $product = Products::where('product_code', $item['product_code'])->first();

            if ($product) {
                $product->update([
                    'base_price' => (int) $item['product_price'],
                    'status' => $status,
                    'raw_response' => $item,
                ]);

                $updated++;
            } else {
                Products::create([
                    'category' => 'prepaid',
                    'product_code' => $item['product_code'],
                    'brand' => $item['product_description'] ?? null,
                    'type' => $item['product_type'] ?? null,
                    'name' => $item['product_nominal'] ?? $item['product_code'],
                    'description' => $item['product_details'] ?? null,
                    'base_price' => (int) $item['product_price'],
                    'price_user' => (int) $item['product_price'],
                    'price_agent' => (int) $item['product_price'],
                    'status' => $status,
                    'raw_response' => $item,
                ]);

                $created++;
            }
        }

        return ['created' => $created, 'updated' => $updated];
    }
}
