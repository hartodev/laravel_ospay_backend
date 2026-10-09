<?php

namespace App\Console\Commands;

use App\Models\Products;
use Illuminate\Console\Command;

class ListUnsyncedProducts extends Command
{
    protected $signature = 'products:unsynced {--deactivate : Set status produk tersebut menjadi inactive}';

    protected $description = 'Daftar produk yang bukan hasil sinkron IAK (dummy/seeder/manual). Default hanya menampilkan.';

    public function handle(): int
    {
        $rows = Products::query()->whereNull('raw_response')->orderBy('id')->get();

        if ($rows->isEmpty()) {
            $this->info('Semua produk berasal dari IAK.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Kode', 'Nama', 'Status'],
            $rows->map(fn ($p) => [$p->id, $p->product_code, $p->name, $p->status])->all()
        );

        if ($this->option('deactivate')) {
            $n = Products::whereNull('raw_response')->where('status', 'active')->update(['status' => 'inactive']);
            $this->info("{$n} produk dinonaktifkan.");
        } else {
            $this->line('Tambahkan --deactivate untuk menonaktifkannya. Produk ini sudah otomatis tidak tampil dan tidak bisa dibeli.');
        }

        return self::SUCCESS;
    }
}
