<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel app_settings sudah dibuat oleh 2026_10_05_000001_create_app_content_tables.
        // Jangan dibuat dua kali (database baru akan error "table already exists").
        if (Schema::hasTable('app_settings')) {
            return;
        }

        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('app_settings')->insert([
            ['key' => 'theme.primary',   'value' => '#1E5BFF', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'theme.secondary', 'value' => '#0B2A8A', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'theme.version',   'value' => '1',       'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
