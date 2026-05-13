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
        Schema::create('favourites', function (Blueprint $table) {
            $table->uuid('id');
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
            $table->uuid('variationid');
            $table->foreign('variationid')
                ->references('id')
                ->on('itemvariations')
                ->cascadeOnDelete();
            $table->enum('status', ['Y', 'N'])->default('Y');
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
        Schema::dropIfExists('favourites');
    }
};