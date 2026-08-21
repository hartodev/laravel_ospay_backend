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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
 
            $table->string('transaction_number')->unique(); // dikirim sebagai order_id ke Midtrans
            $table->bigInteger('amount');
 
            $table->string('payment_method')->default('bank_transfer');
            $table->string('bank')->nullable();  // bca/bni/bri/permata, dst
            $table->string('va_number')->nullable();
 
            $table->enum('status', ['pending', 'success', 'expired', 'cancelled'])->default('pending');
            $table->json('midtrans_response')->nullable();
 
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};