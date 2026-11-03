<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('organizations')->updateOrInsert(
            ['id' => '7c9e6679-7425-40de-944b-e07fc1f90ae7'],
            [
                'name'       => 'Gharma',
                'email'      => 'info@gharma.com',
                'phone'      => '9800000000',
                'address'    => 'Kathmandu, Nepal',
                'logo'       => null,
                'active'     => 'active',
                'status'     => 'Y',
                'current_fiscal_year_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
