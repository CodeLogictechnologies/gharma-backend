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
        Schema::create('itemvariations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('orgid');
            $table->foreign('orgid')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
            $table->uuid('item_id');
            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->cascadeOnDelete();
            $table->string('attribute');
            $table->string('value');
            $table->string('price');
            $table->string('stock');
            $table->string('threshold');
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
        Schema::dropIfExists('itemvariations');
    }
};