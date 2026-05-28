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
        Schema::create('assign_drivers', function (Blueprint $table) {
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
            $table->uuid('driverid')->nullable();
            $table->foreign('driverid')
                ->references('id')->on('users')
                ->nullOnDelete();
            $table->string('order_status')->default('Pending');
            $table->string('deivery_date')->nullable();
            $table->string('status')->default('Y');
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
        Schema::dropIfExists('assign_drivers');
    }
};