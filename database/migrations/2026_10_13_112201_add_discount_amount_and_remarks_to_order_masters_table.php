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
        Schema::table('order_details', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->change();
            $table->decimal('excise_amount', 15, 2)->change();
            $table->decimal('excise_percent', 15, 2)->change();
            $table->decimal('vat_amount', 15, 2)->change();
            $table->decimal('vat_percent', 15, 2)->change();
            $table->decimal('order_detail_total_price', 15, 2)->change();
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
