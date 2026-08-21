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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['superadmin', 'agen', 'user'])->default('user')->after('email');
            $table->enum('status', ['active', 'suspended'])->default('active')->after('role');
              // Kalau user ini didaftarkan/dinaungi oleh agen tertentu
            $table->foreignId('parent_agent_id')->nullable()->after('status')
                ->constrained('users')->nullOnDelete();
 
            $table->string('phone')->nullable()->after('parent_agent_id');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};