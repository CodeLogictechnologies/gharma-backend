<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now   = Carbon::now();
        $roles = [
            ['id' => 1, 'name' => 'Super Admin'],
            ['id' => 2, 'name' => 'Admin'],
            ['id' => 3, 'name' => 'Wholesaler'],
            ['id' => 4, 'name' => 'Retailer'],
            ['id' => 5, 'name' => 'Driver'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'id'         => $role['id'],
                'name'       => $role['name'],
                'status'     => 'Y',
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}