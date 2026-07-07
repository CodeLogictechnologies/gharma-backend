<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now  = Carbon::now();
        $roles = [
            ['id' => '550e8400-e29b-41d4-a716-446655440000', 'name' => 'Super Admin'],
            ['id' => '550e8400-e29b-41d4-a716-446655440001', 'name' => 'Admin'],
            ['id' => '550e8400-e29b-41d4-a716-446655440002', 'name' => 'Wholesaler'],
            ['id' => '550e8400-e29b-41d4-a716-446655440003', 'name' => 'Retailer'],
            ['id' => '550e8400-e29b-41d4-a716-446655440004', 'name' => 'Driver'],
            ['id' => '550e8400-e29b-41d4-a716-446655440005', 'name' => 'Staff'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['id' => $role['id']],
                [
                    'name'       => $role['name'],
                    'status'     => 'Y',
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}