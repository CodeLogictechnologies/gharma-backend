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
        Schema::table('retailer_prices', function (Blueprint $table) {
            $table->decimal('price_before_discount', 15, 2)
                ->nullable()
                ->after('price');

            $table->decimal('price_after_discount', 15, 2)
                ->nullable()
                ->after('price_before_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retailer_prices', function (Blueprint $table) {
            $table->dropColumn([
                'price_before_discount',
                'price_after_discount',
            ]);
        });
    }
};
