<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_masters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('applies_to');
            $table->string('applies_to_id');
            $table->enum('min_requirement', ['none', 'purchase', 'quantity'])->default('none');
            $table->decimal('min_value', 10, 2)->nullable();
            $table->enum('usage_limit_type', ['once', 'limited', 'per_user'])->default('once');
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_limit_per_user')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->date('start_date_ad')->nullable();
            $table->date('end_date_ad')->nullable();
            $table->date('start_date_bs')->nullable();
            $table->date('end_date_bs')->nullable();
            $table->char('status', 1)->default('Y');
            $table->uuid('orgid');
            $table->foreign('orgid')
                ->references('id')->on('organizations')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_masters');
    }
};
