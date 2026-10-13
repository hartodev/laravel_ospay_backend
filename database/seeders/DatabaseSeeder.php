<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Akun contoh (@ppob.test) hanya untuk lokal/staging, TIDAK PERNAH di production.
        if (! app()->environment('production')) {
            $this->call(UserSeeder::class);
        }

        $this->call([
            AppContentSeeder::class,
            // ProductSeeder::class,
        ]);
    }
}
