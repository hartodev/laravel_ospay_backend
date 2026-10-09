<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallets;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@ppob.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
                'status' => 'active',
            ]
        );
        Wallets::firstOrCreate(['user_id' => $superadmin->id], ['balance' => 0]);

        $agen = User::firstOrCreate(
            ['email' => 'agen@ppob.test'],
            [
                'name' => 'Agen Contoh',
                'password' => Hash::make('password'),
                'role' => 'agen',
                'status' => 'active',
            ]
        );
        Wallets::firstOrCreate(['user_id' => $agen->id], ['balance' => 0]);

        // Subdomain toko untuk agen contoh: agen.<domain-utama>
        if (! $agen->subdomain) {
            $agen->forceFill(['subdomain' => 'agen'])->save();
        }

        // User contoh yang berada di bawah toko "agen"
        $member = User::firstOrCreate(
            ['email' => 'member@ppob.test'],
            [
                'name' => 'Member Toko Agen',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'parent_agent_id' => $agen->id,
            ]
        );
        Wallets::firstOrCreate(['user_id' => $member->id], ['balance' => 0]);

        $user = User::firstOrCreate(
            ['email' => 'user@ppob.test'],
            [
                'name' => 'User Contoh',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                // contoh: user ini dinaungi agen di atas
                // 'parent_agent_id' => $agen->id,
            ]
        );
        Wallets::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
    }
}
