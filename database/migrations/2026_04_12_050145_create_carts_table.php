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
        Schema::create('carts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('orgid');
            $table->foreign('orgid')
                ->references('id')
                ->on('organizations')
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
            $table->decimal('unit_price', 10, 2);            // price at time of adding
            $table->decimal('total_price', 10, 2);           // quantity × unit_price
            $table->enum('status', ['Y', 'N'])->default('Y');
            $table->string('postedby')->nullable();
            $table->string('modified')->nullable();
            $table->timestamps();
            $table->softDeletes();                            // deleted_at column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
