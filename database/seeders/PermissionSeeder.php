<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $permissions = [
            'view.favicon', 'update.favicon',
            'add.role', 'edit.role', 'delete.role', 'view.role',
            'view.hometab', 'edit.hometab', 'delete.hometab',
            'view.store', 'edit.store', 'delete.store',
            'view.category', 'edit.category', 'delete.category',
            'view.brand', 'edit.brand', 'delete.brand',
            'view.item', 'edit.item', 'delete.item',
            'view.user', 'edit.user', 'delete.user',
            'view.driverlist', 'edit.driverlist', 'delete.driverlist', 'assign.driver',
            'view.inventory', 'edit.inventory', 'delete.inventory',
            'view.vendor', 'edit.vendor', 'delete.vendor',
            'view.retailer', 'edit.retailer', 'delete.retailer',
            'view.wholesaler', 'edit.wholesaler', 'delete.wholesaler',
            'view.discount', 'edit.discount', 'delete.discount',
            'view.loyalty', 'edit.loyalty', 'delete.loyalty',
            'view.order', 'edit.order', 'delete.order',
            'view.invoice', 'edit.invoice', 'delete.invoice',
            'view.refund', 'edit.refund', 'delete.refund',
            'view.report',
            'view.heatmap',
            'view.notification', 'edit.notification', 'delete.notification',
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert([
                'name'       => $permission,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}