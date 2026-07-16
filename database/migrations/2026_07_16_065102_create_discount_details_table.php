<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_details', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('discount_master_id');
            $table->foreign('discount_master_id')
                ->references('id')->on('discount_masters')
                ->onDelete('cascade');
            $table->uuid('orgid');
            $table->foreign('orgid')
                ->references('id')->on('organizations')
                ->onDelete('cascade');
            $table->uuid('variation_id');
            $table->foreign('variation_id')
                ->references('id')->on('itemvariations')
                ->onDelete('cascade');
            $table->enum('discount_type', ['percentage', 'amount']);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('original_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2);
            $table->decimal('total_amount', 10, 2);

            $table->char('status', 1)->default('Y');
            $table->timestamps();

            $table->index('discount_master_id');
            $table->index('variation_id');

            $table->unique(['discount_master_id', 'variation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_details');
    }
};