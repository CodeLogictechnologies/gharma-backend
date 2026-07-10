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
        Schema::table('purchase_voucher_items', function (Blueprint $table) {
            $table->dropColumn('mrp');
        });

        Schema::table('purchase_return_voucher_items', function (Blueprint $table) {
            $table->dropColumn('mrp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_voucher_items', function (Blueprint $table) {
            $table->decimal('mrp', 12, 2)->nullable();
        });

        Schema::table('purchase_return_voucher_items', function (Blueprint $table) {
            $table->decimal('mrp', 12, 2)->nullable()->after('excise_amount');
        });
    }
};
