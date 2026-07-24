<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('purchase_vouchers', 'fiscal_year_id')) {
            Schema::table('purchase_vouchers', function (Blueprint $table) {
                $table->uuid('fiscal_year_id')->nullable()->after('orgid');
            });
        }

        if (!Schema::hasColumn('purchase_return_vouchers', 'fiscal_year_id')) {
            Schema::table('purchase_return_vouchers', function (Blueprint $table) {
                $table->uuid('fiscal_year_id')->nullable()->after('orgid');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('purchase_vouchers', 'fiscal_year_id')) {
            Schema::table('purchase_vouchers', function (Blueprint $table) {
                $table->dropColumn('fiscal_year_id');
            });
        }

        if (Schema::hasColumn('purchase_return_vouchers', 'fiscal_year_id')) {
            Schema::table('purchase_return_vouchers', function (Blueprint $table) {
                $table->dropColumn('fiscal_year_id');
            });
        }
    }
};