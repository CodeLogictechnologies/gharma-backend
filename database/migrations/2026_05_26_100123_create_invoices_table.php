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
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ordermasterid');
            $table->foreign('ordermasterid')
                ->references('id')
                ->on('order_masters')
                ->cascadeOnDelete();
            $table->uuid('orgid')->nullable();
            $table->foreign('orgid')
                ->references('id')->on('organizations')
                ->nullOnDelete();
            $table->string('invoicenumber')->nullable();
            $table->string('postedby')->nullable();
            $table->string('modified')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};