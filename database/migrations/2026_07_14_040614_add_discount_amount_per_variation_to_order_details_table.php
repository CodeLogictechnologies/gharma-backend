<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->string('discount_type')->nullable();
            $table->decimal('discount_amount', 18, 2)->default(0);
            $table->decimal('discount_amount_per_variation', 18, 2)->default(0);
        });
    }

    public function down()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn([
                'discount_type',
                'discount_amount',
                'discount_amount_per_variation',
            ]);
        });
    }
};
