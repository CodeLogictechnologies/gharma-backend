<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add fiscal_year_id to order_masters (Sales Vouchers)
        Schema::table('order_masters', function (Blueprint $table) {
            $table->uuid('fiscal_year_id')->nullable()->after('remarks');
            $table->index(['orgid', 'fiscal_year_id'], 'idx_om_org_fiscal');
        });

        // Add fiscal_year_id to sales_return_vouchers
        Schema::table('sales_return_vouchers', function (Blueprint $table) {
            $table->uuid('fiscal_year_id')->nullable()->after('total_amount');
            $table->index(['orgid', 'fiscal_year_id'], 'idx_srv_org_fiscal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_masters', function (Blueprint $table) {
            $table->dropIndex('idx_om_org_fiscal');
            $table->dropColumn('fiscal_year_id');
        });

        Schema::table('sales_return_vouchers', function (Blueprint $table) {
            $table->dropIndex('idx_srv_org_fiscal');
            $table->dropColumn('fiscal_year_id');
        });
    }
};