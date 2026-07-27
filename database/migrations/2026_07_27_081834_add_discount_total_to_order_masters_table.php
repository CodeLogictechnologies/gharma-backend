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
        Schema::table('order_masters', function (Blueprint $table) {
            $table->string('payment_status')->nullable();
            $table->decimal('order_master_discount_total', 10, 2)
                ->nullable()
                ->default(0)
                ->after('order_master_subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_masters', function (Blueprint $table) {
            $table->dropColumn('payment_status');
            $table->dropColumn('order_master_discount_total');
        });
    }
};
