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
        Schema::create('category_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('orgid');
            $table->foreign('orgid')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();

            $table->uuid('categoryid');
            $table->foreign('categoryid')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete();

            $table->uuid('itemid');
            $table->foreign('itemid')
                ->references('id')
                ->on('items')
                ->cascadeOnDelete();
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
        Schema::dropIfExists('category_items');
    }
};