<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $roleId = '550e8400-e29b-41d4-a716-446655440000'; // Super Admin role
        $userId = '550e8400-e29b-41d4-a716-446655440000'; // admin@example.com

        // 1. Assign the Super Admin role to the admin user
        DB::table('model_has_roles')->updateOrInsert([
            'role_id'    => $roleId,
            'model_type' => 'App\\Models\\User',
            'model_id'   => $userId,
        ], []);

        // 2. Give the role every existing permission
        $permissionIds = DB::table('permissions')->pluck('id');

        foreach ($permissionIds as $permissionId) {
            DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $permissionId,
                'role_id'       => $roleId,
            ], []);
        }

        // 3. Clear Spatie's permission cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
