<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['prepaid', 'postpaid'])->default('prepaid');
            $table->string('product_code')->unique(); // kode produk dari IAK, contoh: htelkomsel10000
            $table->string('brand')->nullable();       // contoh: TELKOMSEL
            $table->string('type')->nullable();        // contoh: Pulsa, PLN, Game, PDAM
            $table->string('name');
            $table->text('description')->nullable();
 
            $table->bigInteger('base_price');   // harga beli dari IAK
            $table->bigInteger('price_user');   // harga jual ke role user
            $table->bigInteger('price_agent');  // harga jual ke role agen
 
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->json('raw_response')->nullable(); // simpan response asli price list IAK
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};