<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('bg_color', 7)->nullable();
            $table->enum('applies_to', ['item', 'category']);
            $table->unsignedInteger('sort_order')->default(0);
            $table->char('status', 1)->default('Y');
            $table->uuid('orgid');
            $table->uuid('postedby')->nullable();
            $table->uuid('updatedby')->nullable();
            $table->foreign('orgid')
                ->references('id')->on('organizations')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('promotion_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('promotion_id');
            $table->foreign('promotion_id')
                ->references('id')->on('promotions')
                ->cascadeOnDelete();
            $table->uuid('item_id');
            $table->foreign('item_id')
                ->references('id')->on('items')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['promotion_id', 'item_id']);
        });

        Schema::create('promotion_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('promotion_id');
            $table->foreign('promotion_id')
                ->references('id')->on('promotions')
                ->cascadeOnDelete();
            $table->uuid('category_id');
            $table->foreign('category_id')
                ->references('id')->on('categories')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['promotion_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_categories');
        Schema::dropIfExists('promotion_items');
        Schema::dropIfExists('promotions');
    }
};
