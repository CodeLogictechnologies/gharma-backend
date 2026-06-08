<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds product_code and company_product_code to item_variations table.
     * Removes the stock column.
     *
     */
    public function up(): void
    {
        Schema::table('itemvariations', function (Blueprint $table) {
            $table->string('product_code')->nullable()->after('value');
            $table->string('company_product_code')->nullable()->after('product_code');

            // Drop stock column
            $table->dropColumn('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itemvariations', function (Blueprint $table) {
            $table->dropColumn(['product_code', 'company_product_code']);

            // Restore stock column
            $table->unsignedInteger('stock')->default(0)->after('threshold');
        });
    }
};