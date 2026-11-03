<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@example.com'],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440000',
                'name' => 'Administrator',
                'password' => Hash::make('Admin@123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
