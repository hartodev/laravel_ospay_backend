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
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            // ID transaksi internal yang dikirim ke IAK
            $table->string('ref_id')->unique();

            // Nomor HP / ID pelanggan tujuan
            $table->string('customer_id');

            /*
             * wallet = transaksi agen
             * midtrans = transaksi user
             */
            $table->enum('payment_method', [
                'wallet',
                'midtrans',
            ]);

            /*
             * Hanya terisi untuk transaksi user
             * yang menggunakan Midtrans.
             */
            $table->string('midtrans_order_id')->nullable()->unique();

            // Harga beli dari IAK saat transaksi dibuat
            $table->bigInteger('base_price');

            // Harga jual kepada user/agen
            $table->bigInteger('sell_price');

            /*
             * STATUS PEMBAYARAN
             *
             * Untuk USER:
             * pending → paid / expired / cancelled
             *
             * Untuk AGEN:
             * tidak digunakan karena pembayaran
             * dilakukan menggunakan wallet.
             */
            $table->enum('payment_status', [
                'pending',
                'paid',
                'expired',
                'cancelled',
            ])->nullable();

            /*
             * STATUS PROSES IAK
             */
            $table->enum('status', [
                'pending',
                'processing',
                'success',
                'failed',
            ])->default('pending');

            // Serial number dari IAK
            $table->string('sn')->nullable();

            // Alasan kegagalan
            $table->text('failure_reason')->nullable();

            // Response dari IAK
            $table->json('iak_response')->nullable();

            $table->text('qr_url')->nullable();

            // Response dari Midtrans
            $table->json('midtrans_response')->nullable();

            /*
             * REFUND
             *
             * Biasanya hanya relevan untuk:
             * USER + Midtrans + IAK failed
             */
            $table->enum('refund_status', [
                'not_required',
                'pending',
                'processing',
                'refunded',
                'failed',
            ])->default('not_required');

            $table->bigInteger('refund_amount')->nullable();

            $table->text('refund_reason')->nullable();

            $table->timestamp('refunded_at')->nullable();

            // Waktu terakhir check-status IAK
            $table->timestamp('checked_at')->nullable();

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
