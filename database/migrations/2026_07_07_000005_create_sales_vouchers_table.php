<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_vouchers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('orgid');
            $table->foreign('orgid')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();

            $table->date('voucher_date');
            $table->string('voucher_no');

            $table->uuid('customer_id');
            $table->foreign('customer_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            $table->uuid('order_id')->nullable();
            $table->foreign('order_id')
                ->references('id')
                ->on('order_masters')
                ->nullOnDelete();

            $table->text('remarks')->nullable();

            // ── Totals (snapshot at save time) ──────────────────────
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('bill_discount_percent', 5, 2)->default(0);
            $table->decimal('bill_discount_amount', 12, 2)->default(0);
            $table->decimal('taxable_amount', 12, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->enum('status', ['Y', 'N'])->default('Y');
            $table->string('postedby')->nullable();
            $table->string('updatedby')->nullable();

            $table->unique(['orgid', 'voucher_no']);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_vouchers');
    }
};
