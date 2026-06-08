<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add product_code if not exists
        if (!Schema::hasColumn('itemvariations', 'product_code')) {
            Schema::table('itemvariations', function (Blueprint $table) {
                $table->string('product_code')->nullable()->after('value');
            });
        }

        // Add company_product_code if not exists
        if (!Schema::hasColumn('itemvariations', 'company_product_code')) {
            Schema::table('itemvariations', function (Blueprint $table) {
                $table->string('company_product_code')->nullable()->after('product_code');
            });
        }

        // Drop stock only if it still exists
        if (Schema::hasColumn('itemvariations', 'stock')) {
            Schema::table('itemvariations', function (Blueprint $table) {
                $table->dropColumn('stock');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('itemvariations', 'product_code')) {
            Schema::table('itemvariations', function (Blueprint $table) {
                $table->dropColumn('product_code');
            });
        }

        if (Schema::hasColumn('itemvariations', 'company_product_code')) {
            Schema::table('itemvariations', function (Blueprint $table) {
                $table->dropColumn('company_product_code');
            });
        }

        if (!Schema::hasColumn('itemvariations', 'stock')) {
            Schema::table('itemvariations', function (Blueprint $table) {
                $table->unsignedInteger('stock')->default(0)->after('threshold');
            });
        }
    }
};