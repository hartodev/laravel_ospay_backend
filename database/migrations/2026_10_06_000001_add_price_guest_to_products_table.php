<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Harga jual untuk pembeli tamu (tanpa login).
     * NULL = produk tidak dijual ke tamu.
     */
    public function up(): void
    {
        if (Schema::hasColumn('products', 'price_guest')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->bigInteger('price_guest')->nullable()->after('price_agent');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'price_guest')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('price_guest');
            });
        }
    }
};
