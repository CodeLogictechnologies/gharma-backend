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
        Schema::create('order_masters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('orgid');
            $table->foreign('orgid')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
            $table->uuid('userid')->nullable();
            $table->foreign('userid')
                ->references('id')->on('users')
                ->nullOnDelete();
            $table->uuid('addressid');
            $table->foreign('addressid')
                ->references('id')
                ->on('user_addresses')
                ->cascadeOnDelete();
            $table->decimal('order_master_total_price', 10, 2);
            $table->enum('status', ['Y', 'N'])->default('Y');
            $table->enum('order_status', [
                'Pending',
                'Confirmed',
                'Packed',
                'Shipped',
                'Delivered',
                'Cancelled',
                'Returned',
                'Refunded'
            ])->default('Pending');
            $table->string('postedby')->nullable();
            $table->string('modified')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_masters');
    }
};