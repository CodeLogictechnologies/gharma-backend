<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $permissions = [
            'view.favicon', 'update.favicon',
            'add.role', 'edit.role', 'delete.role', 'view.role',

            'add.hometab', 'view.hometab', 'edit.hometab', 'delete.hometab',
            'add.store', 'view.store', 'edit.store', 'delete.store',
            'add.category', 'view.category', 'edit.category', 'delete.category',
            'add.brand', 'view.brand', 'edit.brand', 'delete.brand',
            'add.item', 'view.item', 'edit.item', 'delete.item',
            'add.user', 'view.user', 'edit.user', 'delete.user',
            'add.driverlist', 'view.driverlist', 'edit.driverlist', 'delete.driverlist', 'assign.driver',
            'add.inventory', 'view.inventory', 'edit.inventory', 'delete.inventory',
            'add.vendor', 'view.vendor', 'edit.vendor', 'delete.vendor',
            'add.retailer', 'view.retailer', 'edit.retailer', 'delete.retailer',
            'add.wholesaler', 'view.wholesaler', 'edit.wholesaler', 'delete.wholesaler',
            'add.discount', 'view.discount', 'edit.discount', 'delete.discount',
            'add.coupon', 'view.coupon', 'edit.coupon', 'delete.coupon',
            'add.loyalty', 'view.loyalty', 'edit.loyalty', 'delete.loyalty',
            'add.order', 'view.order', 'edit.order', 'delete.order',
            'add.invoice', 'view.invoice', 'edit.invoice', 'delete.invoice',
            'add.refund', 'view.refund', 'edit.refund', 'delete.refund',

            'view.report',
            'view.heatmap',

            'add.notification', 'view.notification', 'edit.notification', 'delete.notification',
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insertOrIgnore([
                'id'         => Str::uuid()->toString(),
                'name'       => $permission,
                'guard_name' => 'web',
                'status'     => 'Y',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}

