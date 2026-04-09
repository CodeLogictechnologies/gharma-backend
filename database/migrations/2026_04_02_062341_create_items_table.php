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
        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('orgid');
            $table->foreign('orgid')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();

            // $table->uuid('category_id');
            // $table->foreign('category_id')
            //     ->references('id')
            //     ->on('categories')
            //     ->cascadeOnDelete();

            // $table->uuid('subcategory_id');
            // $table->foreign('subcategory_id')
            //     ->references('id')
            //     ->on('sub_categories')
            //     ->cascadeOnDelete();

            $table->uuid('brand_id');
            $table->foreign('brand_id')
                ->references('id')
                ->on('brands')
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('threshold');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['Regular', 'Special', 'Featured'])->default('Regular');
            $table->enum('status', ['Y', 'N'])->default('Y');
            $table->integer('postedby');
            $table->integer('updatedby');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};