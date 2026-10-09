<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();

            /*
             * Satu user hanya boleh mempunyai satu wallet.
             *
             * Dalam business rule kita:
             * wallet hanya untuk agen.
             */
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            /*
             * Total saldo milik agen.
             */
            $table->unsignedBigInteger('balance')
                ->default(0);

            /*
             * Saldo yang sedang ditahan
             * untuk transaksi IAK pending.
             */
            $table->unsignedBigInteger('held_balance')
                ->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
