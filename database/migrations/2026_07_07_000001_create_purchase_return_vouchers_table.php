<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_return_vouchers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('orgid');
            $table->foreign('orgid')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();

            $table->date('return_date');
            $table->string('debit_note_no');

            $table->uuid('vendor_id');
            $table->foreign('vendor_id')
                ->references('id')
                ->on('vendors')
                ->restrictOnDelete();

            $table->uuid('against_voucher_id')->nullable();
            // $table->foreign('against_voucher_id')
            //     ->references('id')
            //     ->on('purchase_vouchers')
            //     ->nullOnDelete();

            $table->text('remarks')->nullable();

            // ── Totals (snapshot at save time) ──────────────────────
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->enum('status', ['Y', 'N'])->default('Y');
            $table->string('postedby')->nullable();
            $table->string('updatedby')->nullable();

            $table->unique(['orgid', 'debit_note_no']);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_return_vouchers');
    }
};