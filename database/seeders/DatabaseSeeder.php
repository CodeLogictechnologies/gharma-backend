<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Userorganization;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            // RolePermissionSeeder::class,
            NepalAddressCoordinateSeeder::class,
            // OrderHeatmapSeeder::class,
            UserSeeder::class,
            OrganizationSeeder::class,
            UserOrganizationSeeder::class,
            SuperAdminSeeder::class
        ]);
    }
}
