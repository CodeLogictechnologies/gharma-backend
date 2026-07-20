<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_vouchers')) {
            Schema::create('purchase_vouchers', function (Blueprint $table) {
                $table->uuid('id')->primary();

                $table->uuid('orgid');
                $table->foreign('orgid')
                    ->references('id')
                    ->on('organizations')
                    ->cascadeOnDelete();

                $table->date('voucher_date');
                $table->string('voucher_no');

                $table->enum('purchase_type', ['trading', 'non_trading_capitalized', 'non_trading_non_capitalized'])
                    ->default('trading');

                $table->uuid('vendor_id');
                $table->foreign('vendor_id')
                    ->references('id')
                    ->on('vendors')
                    ->restrictOnDelete();

                $table->string('pan')->nullable();
                $table->text('remarks')->nullable();

                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('bill_discount_percent', 5, 2)->default(0);
                $table->decimal('bill_discount_amount', 12, 2)->default(0);
                $table->decimal('taxable_amount', 12, 2)->default(0);
                $table->decimal('vat_amount', 12, 2)->default(0);
                $table->decimal('excise_amount', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);

                $table->enum('status', ['Y', 'N'])->default('Y');
                $table->string('postedby')->nullable();
                $table->string('updatedby')->nullable();

                $table->unique(['orgid', 'voucher_no']);

                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_vouchers');
    }
};