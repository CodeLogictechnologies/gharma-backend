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
        Schema::create('discounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->enum('type', ['percentage', 'fixed']);
            $table->decimal('value', 8, 2)->nullable();        // fixed amount
            $table->decimal('percentage', 5, 2)->nullable();   // percentage value
            $table->enum('applies_to', ['entire', 'item', 'variation'])->default('entire');
            $table->uuid('item_id')->nullable();               // if applies to item
            $table->uuid('variation_id')->nullable();          // if applies to variation
            $table->enum('min_requirement', ['none', 'purchase', 'quantity'])->default('none');
            $table->decimal('min_value', 10, 2)->nullable();   // min purchase or qty
            $table->enum('usage_limit_type', ['once', 'limited', 'per_user'])->default('once');
            $table->unsignedInteger('usage_limit')->nullable();          // total uses allowed
            $table->unsignedInteger('usage_limit_per_user')->nullable(); // per customer
            $table->unsignedInteger('used_count')->default(0);           // track usage
            $table->date('starts_at');
            $table->date('ends_at');
            $table->enum('status', ['Y', 'N'])->default('Y');
            $table->string('orgid')->nullable();
            $table->string('discount_type')->nullable();
            $table->string('postedby')->nullable();
            $table->string('updatedby')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};