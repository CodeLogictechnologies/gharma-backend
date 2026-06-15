<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('title')->nullable();
            $table->string('coupon_code', 100)->unique();

            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');

            $table->decimal('percentage', 8, 2)->nullable();
            $table->decimal('value', 10, 2)->nullable();

            $table->enum('applies_to', ['entire', 'item', 'variation'])->default('entire');
            $table->uuid('item_id')->nullable();
            $table->uuid('variation_id')->nullable();

            $table->enum('min_requirement', ['none', 'purchase', 'quantity'])->default('none');
            $table->decimal('min_value', 10, 2)->nullable();

            $table->enum('usage_limit_type', ['once', 'limited', 'per_user'])->default('once');
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_limit_per_user')->nullable();
            $table->integer('used_count')->default(0);

            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();

            $table->char('status', 1)->default('Y');
            $table->uuid('orgid')->nullable();
            $table->uuid('postedby')->nullable();
            $table->uuid('updatedby')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};