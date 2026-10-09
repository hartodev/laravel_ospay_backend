<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\ContentPage;
use App\Models\Faq;
use Illuminate\Database\Seeder;

/**
 * SEEDER UNTUK TES SAJA.
 *
 * Menimpa FAQ, Kebijakan Privasi, Syarat & Ketentuan, dan Tentang Aplikasi
 * dengan teks yang JELAS BERBEDA dari content_defaults.dart. Kalau teks
 * "[TES DB]" muncul di aplikasi, berarti app benar-benar membaca database.
 *
 *     php artisan db:seed --class=ContentTestSeeder
 *
 * Kembalikan ke isi awal dengan:
 *
 *     php artisan db:seed --class=ContentRestoreSeeder
 */
class ContentTestSeeder extends Seeder
{
    public function run(): void
    {
        $stamp = now()->format('d/m H:i:s');

        // ---- FAQ (Pusat Bantuan) -------------------------------------
        Faq::query()->delete();

        $faqs = [
            ["[TES DB] Pertanyaan 1 - dari database ($stamp)", '[TES DB] Jawaban 1. Kalau kamu membaca ini di app, data berasal dari database.'],
            ['[TES DB] Pertanyaan 2 - FAQ ini hanya ada di database', '[TES DB] Jawaban 2. Teks ini tidak ada di content_defaults.dart.'],
            ['[TES DB] Pertanyaan 3 - jumlah FAQ sengaja hanya 3', '[TES DB] Jawaban 3. Isi bawaan punya 7 FAQ, database tes hanya 3.'],
        ];

        foreach ($faqs as $i => [$question, $answer]) {
            Faq::query()->create([
                'question' => $question,
                'answer' => $answer,
                'sort_order' => $i + 1,
                'is_active' => true,
            ]);
        }

        // ---- Halaman dokumen (Kebijakan Privasi & Syarat) -------------
        ContentPage::query()->updateOrCreate(
            ['slug' => 'privacy-policy'],
            [
                'title' => 'Kebijakan Privasi',
                'intro' => "[TES DB] Intro Kebijakan Privasi dari database ($stamp).",
                'is_active' => true,
                'sections' => [
                    [
                        'title' => '[TES DB] Bagian Privasi 1',
                        'paragraphs' => ['[TES DB] Paragraf privasi dari database.'],
                    ],
                    [
                        'title' => '[TES DB] Bagian Privasi 2',
                        'paragraphs' => [
                            '[TES DB] Paragraf kedua.',
                            '[TES DB] Paragraf ketiga.',
                        ],
                    ],
                ],
            ]
        );

        ContentPage::query()->updateOrCreate(
            ['slug' => 'terms'],
            [
                'title' => 'Syarat & Ketentuan',
                'intro' => "[TES DB] Intro Syarat & Ketentuan dari database ($stamp).",
                'is_active' => true,
                'sections' => [
                    [
                        'title' => '[TES DB] Bagian Syarat 1',
                        'paragraphs' => ['[TES DB] Paragraf syarat dari database.'],
                    ],
                    [
                        'title' => '[TES DB] Bagian Syarat 2',
                        'paragraphs' => ['[TES DB] Paragraf syarat kedua.'],
                    ],
                ],
            ]
        );

        // ---- Tentang Aplikasi + kontak Pusat Bantuan ------------------
        AppSetting::setValue('app.info', [
            'description' => '[TES DB] Deskripsi aplikasi dari database.',
            'latest_version' => '9.9.9', // memunculkan badge "Versi terbaru tersedia"
            'support' => [
                'whatsapp' => '0899-0000-1111',
                'email' => 'tes-db@ospay.test',
                'hours' => '[TES DB] Senin - Jumat, 09.00 - 17.00',
            ],
            'highlights' => [
                [
                    'icon' => 'bolt',
                    'title' => '[TES DB] Poin unggulan 1',
                    'description' => '[TES DB] Deskripsi poin 1 dari database.',
                ],
                [
                    'icon' => 'wallet',
                    'title' => '[TES DB] Poin unggulan 2',
                    'description' => '[TES DB] Deskripsi poin 2 dari database.',
                ],
            ],
        ]);

        $this->command?->info('Konten TES dimasukkan. Cek di app: harus muncul teks "[TES DB]".');
    }
}
