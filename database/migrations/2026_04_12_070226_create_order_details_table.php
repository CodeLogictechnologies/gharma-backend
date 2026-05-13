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
        Schema::create('order_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ordermasterid');

            $table->foreign('ordermasterid')
                ->references('id')
                ->on('order_masters')
                ->cascadeOnDelete();
            $table->uuid('variation_id')->nullable();
            $table->foreign('variation_id')
                ->references('id')->on('itemvariations')
                ->nullOnDelete();
            $table->uuid('userid')->nullable();
            $table->foreign('userid')
                ->references('id')->on('users')
                ->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('price');

            $table->decimal('order_detail_total_price', 10, 2);
            $table->enum('status', ['Y', 'N'])->default('Y');
            $table->string('postedby')->nullable();
            $table->string('modified')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};