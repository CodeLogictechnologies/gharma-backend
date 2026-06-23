<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Get all permissions inserted by PermissionSeeder
        $allPermissions = Permission::all();

        // Give Super Admin all permissions
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions($allPermissions);
        }

        // Give Admin all permissions except role management
        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            $adminPermissions = $allPermissions->filter(fn($p) => !in_array($p->name, [
                'add.role', 'edit.role', 'delete.role',
            ]));
            $admin->syncPermissions($adminPermissions);
        }

        // Assign Super Admin role to first user
        $user = User::first();
        if ($user) {
            $user->assignRole('Super Admin');
        }
    }
}