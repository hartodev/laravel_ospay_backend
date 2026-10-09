<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Subdomain toko (hanya diisi untuk role agen) + nama toko.
            $table->string('subdomain', 63)->nullable()->unique()->after('phone');
            $table->string('store_name')->nullable()->after('subdomain');
        });

        // Agen yang sudah ada: beri subdomain otomatis dari namanya.
        $reserved = config('tenancy.reserved', []);
        $taken = [];

        DB::table('users')->where('role', 'agen')->orderBy('id')->get(['id', 'name'])
            ->each(function ($agent) use (&$taken, $reserved) {
                $base = substr(trim(Str::slug($agent->name), '-'), 0, 30);
                if (strlen($base) < 3) {
                    $base = 'toko-' . ($base !== '' ? $base : $agent->id);
                }

                $candidate = $base;
                $i = 2;
                while (in_array($candidate, $reserved, true) || isset($taken[$candidate])) {
                    $suffix = '-' . $i++;
                    $candidate = substr($base, 0, 30 - strlen($suffix)) . $suffix;
                }

                $taken[$candidate] = true;

                DB::table('users')->where('id', $agent->id)->update([
                    'subdomain' => $candidate,
                    'store_name' => $agent->name,
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['subdomain']);
            $table->dropColumn(['subdomain', 'store_name']);
        });
    }
};
