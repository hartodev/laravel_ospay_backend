<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('email')
                ->unique();

            $table->timestamp('email_verified_at')
                ->nullable();

            $table->string('password');

            /*
             * ========================================================
             * ROLE
             * ========================================================
             */
            $table->enum('role', [
                'superadmin',
                'agen',
                'user',
            ])->default('user');

            /*
             * ========================================================
             * ACCOUNT STATUS
             * ========================================================
             *
             * user:
             *   active
             *
             * agen:
             *   pending → active → suspended
             *
             * superadmin:
             *   active
             */
            $table->enum('status', [
                'pending',
                'active',
                'suspended',
            ])->default('active');

            /*
             * ========================================================
             * PARENT AGENT
             * ========================================================
             *
             * Kalau user didaftarkan oleh agen,
             * user dapat parent_agent_id.
             */
            $table->foreignId('parent_agent_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('phone')
                ->nullable();

            $table->rememberToken();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
