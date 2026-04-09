<?php

// database/seeders/RolePermissionSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = ['post.view', 'post.create', 'post.edit', 'post.delete'];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo($permissions);

        $editor = Role::firstOrCreate(['name' => 'editor']);
        $editor->givePermissionTo(['post.view', 'post.create', 'post.edit']);

        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->givePermissionTo(['post.view']);

        // Assign admin role to first user
        $user = User::first();
        if ($user) {
            $user->assignRole('admin');
        }
    }
}