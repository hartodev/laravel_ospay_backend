<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // MySQL gak bisa langsung nambah value enum lewat Blueprint biasa,
            // jadi pakai raw statement.
            DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active', 'pending', 'suspended') NOT NULL DEFAULT 'active'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            DB::statement("UPDATE users SET status = 'suspended' WHERE status = 'pending'");
            DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active', 'suspended') NOT NULL DEFAULT 'active'");
        });
    }
};
