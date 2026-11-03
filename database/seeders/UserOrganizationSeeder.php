<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('userorganizations')->updateOrInsert(
            [
                'userid' => '550e8400-e29b-41d4-a716-446655440000', // admin user
                'orgid'  => '7c9e6679-7425-40de-944b-e07fc1f90ae7', // Gharma org
            ],
            [
                'id'         => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
                'status'     => 'Y',
                'postedby'   => 'seeder',
                'modified'   => 'seeder',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
