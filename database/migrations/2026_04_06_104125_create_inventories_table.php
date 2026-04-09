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
        Schema::create('inventories', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('item_id');
            $table->foreign('item_id')
                ->references('id')->on('items')
                ->cascadeOnDelete();

            $table->uuid('variation_id')->nullable();
            $table->foreign('variation_id')
                ->references('id')->on('itemvariations')
                ->nullOnDelete();

            $table->uuid('vendor_id')->nullable();
            $table->foreign('vendor_id')
                ->references('id')->on('vendors')
                ->nullOnDelete();

            $table->string('sku')->nullable()->unique();
            $table->string('barcode')->nullable();
            $table->string('location')->nullable();

            // ── Stock Levels ─────────────────────────────────────────
            $table->decimal('quantity_in_hand', 12, 2)->default(0);
            $table->decimal('quantity_reserved', 12, 2)->default(0);  // pending orders
            $table->decimal('quantity_available', 12, 2)->default(0); // in_hand - reserved

            // ── Reorder Settings ─────────────────────────────────────
            $table->decimal('reorder_level', 12, 2)->default(0);      // alert threshold
            $table->decimal('reorder_quantity', 12, 2)->default(0);   // how much to reorder

            // ── Pricing ──────────────────────────────────────────────
            $table->decimal('unit_cost', 12, 2)->default(0);          // purchase cost
            $table->decimal('selling_price', 12, 2)->default(0);      // sale price

            $table->enum('status', ['Y', 'N'])->default('Y');

            $table->string('manufacturedatead')->nullable();
            $table->string('manufacturedatebs')->nullable();
            $table->string('expirydatead')->nullable();
            $table->string('expirydatebs')->nullable();
            // ── Relations ────────────────────────────────────────────
            $table->uuid('orgid');
            $table->foreign('orgid')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
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
        Schema::dropIfExists('inventories');
    }
};