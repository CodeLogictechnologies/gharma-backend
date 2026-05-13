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
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('customerid');
            $table->foreign('customerid')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->uuid('orgid');
            $table->foreign('orgid')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
            $table->uuid('ordermasterid');
            $table->foreign('ordermasterid')
                ->references('id')
                ->on('order_masters')
                ->cascadeOnDelete();
            $table->enum('status', ['Y', 'N'])->default('Y');
            $table->enum('order_status', ['Pending', 'Processing', 'Delivered', 'Cancelled'])
                ->default('Pending');
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
        Schema::dropIfExists('order_statuses');
    }
};