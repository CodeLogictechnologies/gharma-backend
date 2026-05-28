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
        Schema::create('loyalty_setups', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('userid');
            $table->foreign('userid')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->uuid('orgid')->nullable();
            $table->foreign('orgid')
                ->references('id')->on('organizations')
                ->nullOnDelete();
            $table->string('minprice');
            $table->string('maxprice');
            $table->string('percentage');
            $table->enum('status', ['Y', 'N'])->default('Y');
            $table->string('postedby')->nullable();
            $table->string('updatedby')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_setups');
    }
};