<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_return_vouchers', function (Blueprint $table) {
            $table->decimal('bill_discount_percent', 5, 2)->default(0)->after('subtotal');
            $table->decimal('bill_discount_amount', 12, 2)->default(0)->after('bill_discount_percent');
            $table->decimal('taxable_amount', 12, 2)->default(0)->after('bill_discount_amount');
            $table->decimal('excise_amount', 12, 2)->default(0)->after('vat_amount');
        });

        Schema::table('purchase_return_voucher_items', function (Blueprint $table) {
            $table->enum('excise_type', ['percentage', 'fixed'])->nullable()->after('vat_amount');
            $table->decimal('excise_percentage', 5, 2)->nullable()->after('excise_type');
            $table->decimal('excise_value', 8, 2)->nullable()->after('excise_percentage');
            $table->decimal('excise_amount', 12, 2)->default(0)->after('excise_value');
            $table->decimal('mrp', 12, 2)->nullable()->after('excise_amount');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_return_vouchers', function (Blueprint $table) {
            $table->dropColumn(['bill_discount_percent', 'bill_discount_amount', 'taxable_amount', 'excise_amount']);
        });

        Schema::table('purchase_return_voucher_items', function (Blueprint $table) {
            $table->dropColumn(['excise_type', 'excise_percentage', 'excise_value', 'excise_amount', 'mrp']);
        });
    }
};
