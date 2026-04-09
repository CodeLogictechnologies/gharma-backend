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
        Schema::create('wholesaler_price_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('orgid');
            $table->foreign('orgid')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
            $table->uuid('wholesalermasterid');
            $table->foreign('wholesalermasterid')
                ->references('id')
                ->on('wholesaler_prices')
                ->cascadeOnDelete();
            $table->integer('min_qty');
            $table->integer('max_qty');
            $table->decimal('price', 10, 2);
            $table->enum('status', ['Y', 'N'])->default('Y');

            $table->integer('postedby');
            $table->integer('updatedby')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wholesaler_price_details');
    }
};
