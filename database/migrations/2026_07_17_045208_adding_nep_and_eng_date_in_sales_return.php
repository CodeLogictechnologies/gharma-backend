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
        Schema::table('sales_return_vouchers', function (Blueprint $table) {
            $table->string('sales_retrun_vouchers_date_eng')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_return_vouchers', function (Blueprint $table) {
            $table->dropColumn([
                'sales_retrun_vouchers_date_eng',
            ]);
        });
    }
};
