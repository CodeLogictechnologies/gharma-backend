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
        Schema::create('vendors', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();

            $table->string('company_name')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('registration_number')->nullable();

            $table->string('address')->nullable();
            $table->string('city')->nullable();
            // $table->string('state')->nullable();
            // $table->string('country')->nullable();
            // $table->string('zip_code')->nullable();

            // ── Status & Flags ───────────────────────────────────────
            $table->enum('status', ['Y', 'N'])->default('Y');

            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            $table->uuid('orgid');
            $table->foreign('orgid')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
            $table->integer('postedby');
            $table->integer('updatedby')->nullable();


            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};