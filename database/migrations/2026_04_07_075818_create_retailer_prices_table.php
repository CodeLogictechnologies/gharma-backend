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
        Schema::create('retailer_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('orgid');
            $table->foreign('orgid')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
            $table->uuid('itemid');
            $table->foreign('itemid')
                ->references('id')
                ->on('items')
                ->cascadeOnDelete();
            $table->uuid('variation_id')->nullable();
            $table->foreign('variation_id')
                ->references('id')->on('itemvariations')
                ->nullOnDelete();
            $table->decimal('price', 12, 2)->default(0);
            $table->enum('status', ['Y', 'N'])->default('Y');

            $table->string('postedby')->nullable();
            $table->string('updatedby')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retailer_prices');
    }
};
