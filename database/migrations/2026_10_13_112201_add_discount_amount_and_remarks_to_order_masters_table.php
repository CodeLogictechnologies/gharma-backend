<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill NULLs for every column being tightened below
        DB::table('order_details')->update([
            'price'                    => DB::raw('COALESCE(price, 0)'),
            'excise_amount'            => DB::raw('COALESCE(excise_amount, 0)'),
            'excise_percent'           => DB::raw('COALESCE(excise_percent, 0)'),
            'vat_amount'               => DB::raw('COALESCE(vat_amount, 0)'),
            'vat_percent'              => DB::raw('COALESCE(vat_percent, 0)'),
            'order_detail_total_price' => DB::raw('COALESCE(order_detail_total_price, 0)'),
        ]);

        Schema::table('order_details', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->default(0)->change();
            $table->decimal('excise_amount', 15, 2)->default(0)->change();
            $table->decimal('excise_percent', 15, 2)->default(0)->change();
            $table->decimal('vat_amount', 15, 2)->default(0)->change();
            $table->decimal('vat_percent', 15, 2)->default(0)->change();
            $table->decimal('order_detail_total_price', 15, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
            $table->decimal('excise_amount', 10, 2)->change();
            $table->decimal('excise_percent', 10, 2)->change();
            $table->decimal('vat_amount', 10, 2)->change();
            $table->decimal('vat_percent', 10, 2)->change();
            $table->decimal('order_detail_total_price', 10, 2)->change();
        });
    }
};