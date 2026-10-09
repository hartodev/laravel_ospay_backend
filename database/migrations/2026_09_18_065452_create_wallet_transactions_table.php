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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wallet_id')
                ->constrained('wallets')
                ->cascadeOnDelete();

            /*
             * ========================================================
             * ARAH PERUBAHAN SALDO
             * ========================================================
             *
             * credit = saldo bertambah (deposit, refund, komisi)
             * debit  = saldo berkurang (pembelian PPOB, penalti, dll)
             */
            $table->enum('type', [
                'credit',
                'debit',
            ]);

            /*
             * ========================================================
             * SUMBER PERUBAHAN (referensi)
             * ========================================================
             *
             * Contoh: 'deposit', 'transaction', 'refund', 'commission', 'adjustment'
             *
             * reference_type + reference_id menunjuk ke record asal
             * perubahan saldo ini (mis. deposits.id atau transactions.id).
             * Sengaja TIDAK dibuat sebagai foreign key karena bisa
             * menunjuk ke tabel yang berbeda-beda tergantung 'reference_type'.
             */
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            // Nilai perubahan saldo, selalu POSITIF. Arah ditentukan oleh 'type'.
            $table->unsignedBigInteger('amount');

            // Snapshot saldo sebelum & sesudah — inti dari audit trail.
            $table->unsignedBigInteger('balance_before');
            $table->unsignedBigInteger('balance_after');

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
