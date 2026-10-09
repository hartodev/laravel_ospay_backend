<?php

namespace App\Console\Commands;

use App\Services\PricelistImportService;
use Illuminate\Console\Command;
use Throwable;

class ImportPriceList extends Command
{
    protected $signature = 'products:import-pricelist {path : Lokasi file pricelist .xlsx}';

    protected $description = 'Import produk prepaid dari file pricelist IAK (xlsx).';

    public function handle(PricelistImportService $service): int
    {
        try {
            $r = $service->import($this->argument('path'));
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Import selesai ({$r['sheets']} sheet). Baru: {$r['created']}, diupdate: {$r['updated']}.");

        if ($r['skipped_sheets']) {
            $this->warn('Sheet dilewati: ' . implode(', ', $r['skipped_sheets']));
        }

        return self::SUCCESS;
    }
}
