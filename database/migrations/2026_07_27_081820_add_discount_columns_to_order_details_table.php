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
            $table->string('variation_discount_type')->nullable()->after('price');
            $table->string('excise_percent')->nullable()->after('variation_discount_type');
            $table->decimal('variation_discount_amount', 10, 2)->nullable()->default(0)->after('variation_discount_type');

            $table->string('campaign_discount_type')->nullable()->after('variation_discount_amount');
            $table->decimal('campaign_discount_amount', 10, 2)->nullable()->default(0)->after('campaign_discount_type');

            $table->decimal('total_discount_amount', 10, 2)->nullable()->default(0)->after('campaign_discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn([
                'excise_percent',
                'variation_discount_type',
                'variation_discount_amount',
                'campaign_discount_type',
                'campaign_discount_amount',
                'total_discount_amount',
            ]);
        });
    }
};