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
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('orgid');
            $table->foreign('orgid')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();

            $table->uuid('userid')->nullable();
            $table->foreign('userid')
                ->references('id')->on('users')
                ->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('name')->nullable();
            $table->string('address_name');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('type', ['home', 'work', 'other', 'campus'])->default('home');
            $table->string('other_address_name')->nullable();
            $table->enum('status', ['Y', 'N'])->default('Y');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};