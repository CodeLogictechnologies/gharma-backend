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
            'add.role', 'edit.role', 'delete.role', 'view.role',

            // Organization
            'add.organization', 'view.organization', 'edit.organization', 'delete.organization',

            // Organization Access
            'add.organization-access', 'view.organization-access', 'edit.organization-access', 'delete.organization-access',

            // Organization Roles
            'add.organization-role', 'view.organization-role', 'edit.organization-role', 'delete.organization-role',

            // Permission
            'add.permission', 'view.permission', 'edit.permission', 'delete.permission',

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

            // Fiscal Year Setup
            'add.fiscalyear', 'view.fiscalyear', 'edit.fiscalyear', 'delete.fiscalyear',

            // Org Fiscal Years
            'add.org-fiscalyear', 'view.org-fiscalyear', 'edit.org-fiscalyear', 'delete.org-fiscalyear',

            // Variation Attribute
            'add.variation-attribute', 'view.variation-attribute', 'edit.variation-attribute', 'delete.variation-attribute',

            // Unit
            'add.unit', 'view.unit', 'edit.unit', 'delete.unit',

            // Driver (separate from driverlist, matches sidebar's 'Drive' menu)
            'add.driver', 'view.driver', 'edit.driver', 'delete.driver',
            'view.assign-driver',

            // Purchase Voucher / Purchase Return
            'add.purchase-voucher', 'view.purchase-voucher', 'edit.purchase-voucher', 'delete.purchase-voucher',
            'add.purchase-return', 'view.purchase-return', 'edit.purchase-return', 'delete.purchase-return',

            // Sales / Sales Return
            'add.sales', 'view.sales', 'edit.sales', 'delete.sales',
            'add.sales-return', 'view.sales-return', 'edit.sales-return', 'delete.sales-return',

            // Policies
            'view.refunds-policy',
            'view.terms-conditions',

            // Reports
            'view.report-sales',
            'view.inventory-report',
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