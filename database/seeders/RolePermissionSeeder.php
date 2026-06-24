<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear permission cache first
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $allPermissions = Permission::all();

        if ($allPermissions->isEmpty()) {
            $this->command->warn('No permissions found. Run PermissionSeeder first.');
            return;
        }

        // Find or create Super Admin role
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($allPermissions);

        // Find or create Admin role
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminPermissions = $allPermissions->filter(
            fn($p) => !in_array($p->name, ['add.role', 'edit.role', 'delete.role'])
        );
        $admin->syncPermissions($adminPermissions);

        // Assign Super Admin role to first user
        $user = User::first();
        if ($user) {
            $user->assignRole('Super Admin');
        }
    }
}