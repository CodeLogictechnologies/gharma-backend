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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('customer_id');
            $table->foreign('customer_id')
                ->references('id')
                ->on('profiles')
                ->cascadeOnDelete();

            $table->uuid('item_id');
            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->cascadeOnDelete();

            $table->uuid('variation_id');
            $table->foreign('variation_id')
                ->references('id')
                ->on('itemvariations')
                ->cascadeOnDelete();

            $table->integer('qty');
            $table->decimal('price', 10, 2);
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
        Schema::dropIfExists('orders');
    }
};