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
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            /*
             * Jika NULL, rule berlaku general (semua produk).
             * Jika diisi, rule hanya berlaku untuk produk tersebut.
             *
             * Saat pencarian rule nanti: cari rule khusus produk dulu,
             * baru fallback ke rule general (product_id = NULL).
             */
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->cascadeOnDelete();

            /*
             * Role penerima komisi. Saat ini hanya agen yang menerima
             * komisi. Dibuat enum (bukan kolom bebas) agar konsisten,
             * tapi mudah ditambah value baru (mis. 'master_agent') lewat
             * migration kecil kalau nanti multi-level agent jadi dipakai.
             */
            $table->enum('role', [
                'agen',
            ])->default('agen');

            /*
             * percentage = 'value' dianggap persen dari margin (0-100)
             * nominal    = 'value' dianggap nominal rupiah tetap
             */
            $table->enum('type', [
                'percentage',
                'nominal',
            ]);

            $table->decimal('value', 10, 2);

            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');

            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();

            $table->timestamps();

            $table->index(['product_id', 'role', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_rules');
    }
};
