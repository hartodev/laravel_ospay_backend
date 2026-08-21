<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;

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
        Wallet::firstOrCreate(['user_id' => $superadmin->id], ['balance' => 0]);
 
        $agen = User::firstOrCreate(
            ['email' => 'agen@ppob.test'],
            [
                'name' => 'Agen Contoh',
                'password' => Hash::make('password'),
                'role' => 'agen',
                'status' => 'active',
            ]
        );
        Wallet::firstOrCreate(['user_id' => $agen->id], ['balance' => 0]);
 
        $user = User::firstOrCreate(
            ['email' => 'user@ppob.test'],
            [
                'name' => 'User Contoh',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                // contoh: user ini dinaungi agen di atas
                'parent_agent_id' => $agen->id,
            ]
        );
        Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
    }
}