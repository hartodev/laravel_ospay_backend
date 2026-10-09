<?php

namespace App\Console\Commands;

use App\Services\Iak\ProductSyncService;
use Illuminate\Console\Command;
use RuntimeException;

class SyncPriceList extends Command
{
    protected $signature = 'products:sync-pricelist';

    protected $description = 'Sinkronisasi base_price dan status produk prepaid dari IAK.';

    public function handle(ProductSyncService $productSyncService): int
    {
        try {
            $result = $productSyncService->sync();
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info(
            "Sync selesai. Produk baru: {$result['created']}, produk diupdate: {$result['updated']}."
        );

        return self::SUCCESS;
    }
}
