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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('userid');
            $table->foreign('userid')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->uuid('orgid');
            $table->foreign('orgid')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
            $table->string('txncode');
            $table->string('method');

            $table->enum('status', ['Y', 'N'])->default('Y');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
