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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
 
            $table->string('ref_id')->unique(); // dikirim sebagai ref_id ke IAK, harus unik
            $table->string('customer_id');      // nomor hp / id pelanggan tujuan
 
            $table->bigInteger('base_price'); // harga beli dari IAK saat transaksi terjadi
            $table->bigInteger('sell_price'); // harga yang dipotong dari saldo user
 
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->string('sn')->nullable();          // serial number / bukti dari provider
            $table->text('failure_reason')->nullable();
            $table->json('iak_response')->nullable();
 
            $table->timestamp('checked_at')->nullable(); // terakhir kali dicek via check-status
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};