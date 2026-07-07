<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_return_voucher_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('orgid');
            $table->foreign('orgid')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();

            $table->uuid('purchase_return_voucher_id');
            $table->foreign('purchase_return_voucher_id')
                ->references('id')
                ->on('purchase_return_vouchers')
                ->cascadeOnDelete();

            $table->uuid('item_id');
            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->restrictOnDelete();

            $table->uuid('variation_id')->nullable();
            $table->foreign('variation_id')
                ->references('id')
                ->on('itemvariations')
                ->nullOnDelete();

            $table->string('unit')->nullable();
            $table->decimal('qty', 12, 2);
            $table->decimal('unit_rate', 12, 2);
            $table->decimal('amount', 12, 2)->default(0);

            // ── VAT snapshot (from item's vat_status at time of return) ──
            $table->decimal('vat_percent', 5, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);

            $table->decimal('net_amount', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_return_voucher_items');
    }
};
