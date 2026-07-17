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
        Schema::create('units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('orgid');
            $table->string('unit_name');
            $table->string('unit_short_name')->nullable();
            $table->enum('status', ['Y', 'N'])->default('Y');
            $table->uuid('postedby')->nullable();
            $table->uuid('updatedby')->nullable();
            $table->timestamps();

            $table->foreign('orgid')->references('id')->on('organizations')->onDelete('cascade');
            $table->unique(['orgid', 'unit_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};