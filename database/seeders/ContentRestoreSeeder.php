<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\ContentPage;
use App\Models\Faq;
use Illuminate\Database\Seeder;

/**
 * Mengembalikan konten ke isi awal (setelah ContentTestSeeder).
 *
 * Menghapus FAQ, dua halaman dokumen, dan app.info, lalu menjalankan ulang
 * AppContentSeeder yang akan membuatnya kembali dari isi awal.
 *
 *     php artisan db:seed --class=ContentRestoreSeeder
 *
 * PERINGATAN: semua edit manual lewat /admin/content pada keempat data
 * itu ikut terhapus.
 */
class ContentRestoreSeeder extends Seeder
{
    public function run(): void
    {
        Faq::query()->delete();
        ContentPage::query()->whereIn('slug', ['privacy-policy', 'terms'])->delete();
        AppSetting::query()->where('key', 'app.info')->delete();

        $this->call(AppContentSeeder::class);

        $this->command?->info('Konten dikembalikan ke isi awal.');
    }
}
