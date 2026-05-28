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
        Schema::create('loyalties', function (Blueprint $table) {
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
            $table->uuid('order_detail_id');
            $table->foreign('order_detail_id')
                ->references('id')
                ->on('order_details')
                ->cascadeOnDelete();
            $table->string('loyaltypoint');
            $table->enum('status', ['Y', 'N'])->default('Y');
            $table->string('order_status')->default('Pending');
            $table->string('postedby')->nullable();
            $table->string('updatedby')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalties');
    }
};
