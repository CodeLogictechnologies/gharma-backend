<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds product_code and company_product_code columns to the items table.
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('product_code')->nullable()->after('title');
            $table->string('company_product_code')->nullable()->after('product_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['product_code', 'company_product_code']);
        });
    }
};