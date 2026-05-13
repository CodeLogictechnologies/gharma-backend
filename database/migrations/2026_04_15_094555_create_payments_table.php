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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('userid');
            $table->foreign('userid')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->string('transaction_code');
            $table->string('status');
            $table->decimal('total_amount', 10, 2);
            $table->string('transaction_uuid')->unique();
            $table->string('method');
            $table->string('signed_field_names')->nullable();
            $table->string('signature')->nullable();
            $table->string('product_code')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
