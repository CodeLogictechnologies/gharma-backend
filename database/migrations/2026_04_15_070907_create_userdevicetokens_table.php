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
        Schema::create('userdevicetokens', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('userid');
            $table->foreign('userid')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->string('devicetoken');
            $table->string('devicename')->nullable();
            $table->string('devicetype')->nullable();
            $table->string('mobilenumber');
            $table->enum('status', ['Y', 'N'])->default('Y');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('userdevicetokens');
    }
};
