<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat Akun Pengguna 1
        User::firstOrCreate(
            ['email' => 'user1@example.com'],
            [
                'name' => 'User 01',
                'password' => Hash::make('password123'),
            ]
        );

        // Buat Akun Pengguna 2 (untuk verifikasi isolasi data / Policy)
        User::firstOrCreate(
            ['email' => 'user2@example.com'],
            [
                'name' => 'User 02',
                'password' => Hash::make('password123'),
            ]
        );
    }
}