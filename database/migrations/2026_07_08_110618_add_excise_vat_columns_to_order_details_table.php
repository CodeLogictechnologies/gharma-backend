<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_masters', function (Blueprint $table) {
            $table->decimal('order_master_subtotal', 12, 2)->default(0)->after('order_master_total_price');
            $table->decimal('order_master_excise_total', 12, 2)->default(0)->after('order_master_subtotal');
            $table->decimal('order_master_vat_total', 12, 2)->default(0)->after('order_master_excise_total');
            $table->string('user_type')->nullable()->after('order_master_vat_total');
        });
    }

    public function down(): void
    {
        Schema::table('order_masters', function (Blueprint $table) {
            $table->dropColumn([
                'order_master_subtotal',
                'order_master_excise_total',
                'order_master_vat_total',
            ]);
        });
    }
};
