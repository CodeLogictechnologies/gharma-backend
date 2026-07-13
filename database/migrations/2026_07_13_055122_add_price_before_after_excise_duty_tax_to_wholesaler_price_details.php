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
        Schema::table('wholesaler_price_details', function (Blueprint $table) {
            $table->decimal('price_before_excise_tax', 10, 2)->nullable()->after('price');
            $table->decimal('price_after_excise_tax', 10, 2)->nullable()->after('price_before_excise_tax');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wholesaler_price_details', function (Blueprint $table) {
            $table->dropColumn([
                'price_before_excise_tax',
                'price_after_excise_tax',
            ]);
        });
    }
};