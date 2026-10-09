<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_themes', function (Blueprint $table) {
            $table->id();
            $table->string('primary', 7)->default('#2563EB');
            $table->string('primary_dark', 7)->default('#1D4ED8');
            $table->string('primary_light', 7)->default('#EFF4FF');
            $table->string('on_primary', 7)->default('#FFFFFF');
            $table->string('secondary', 7)->default('#0D9488');
            $table->string('on_secondary', 7)->default('#FFFFFF');
            // Naik 1 setiap kali disimpan; aplikasi mobile hanya mengganti
            // warna kalau nilai ini berbeda dari yang sedang dipakai.
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_themes');
    }
};
