<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Data contoh manual dulu. Nanti di Step 6 kita buat PriceListService
        // yang sync otomatis dari endpoint Price List IAK dan isi/update tabel ini.
        $products = [
            [
                'category' => 'prepaid',
                'product_code' => 'htelkomsel10000',
                'brand' => 'TELKOMSEL',
                'type' => 'Pulsa',
                'name' => 'Telkomsel 10.000',
                'base_price' => 10500,
                'price_user' => 11500,
                'price_agent' => 11000,
            ],
            [
                'category' => 'prepaid',
                'product_code' => 'htelkomsel25000',
                'brand' => 'TELKOMSEL',
                'type' => 'Pulsa',
                'name' => 'Telkomsel 25.000',
                'base_price' => 25200,
                'price_user' => 26500,
                'price_agent' => 25900,
            ],
            [
                'category' => 'prepaid',
                'product_code' => 'pln20',
                'brand' => 'PLN',
                'type' => 'PLN',
                'name' => 'Token PLN 20.000',
                'base_price' => 20250,
                'price_user' => 21500,
                'price_agent' => 20900,
            ],
            [
                'category' => 'postpaid',
                'product_code' => 'PDAMKOTA.SURABAYA',
                'brand' => 'PDAM',
                'type' => 'PDAM',
                'name' => 'PDAM Kota Surabaya',
                'base_price' => 0, // postpaid, tagihan baru diketahui setelah inquiry
                'price_user' => 3000, // biaya admin
                'price_agent' => 2000,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['product_code' => $product['product_code']],
                $product + ['status' => 'active']
            );
        }
    }
}