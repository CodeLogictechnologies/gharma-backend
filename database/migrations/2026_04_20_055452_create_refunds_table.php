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
        Schema::create('refunds', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('userid');
            $table->foreign('userid')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->uuid('order_detail_id');
            $table->foreign('order_detail_id')
                ->references('id')
                ->on('order_details')
                ->cascadeOnDelete();
            $table->uuid('variationid');
            $table->foreign('variationid')
                ->references('id')
                ->on('itemvariations')
                ->cascadeOnDelete();
            $table->text('reason');
            $table->enum('refund_status', [
                'PENDING',
                'UNDER_REVIEW',
                'APPROVED',
                'REJECTED',
                'PROCESSING',
                'COMPLETED',
                'CANCELLED'
            ])->default('PENDING');
            $table->string('type')->nullable();
            $table->enum('status', ['Y', 'N'])->default('Y');
            $table->string('postedby')->nullable();
            $table->string('updatedby')->nullable();
            $table->text('admin_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};