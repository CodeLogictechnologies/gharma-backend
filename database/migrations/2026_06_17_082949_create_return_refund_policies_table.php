<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_refund_policies', function (Blueprint $table) {
            $table->uuid();
            $table->enum('type', ['return', 'refund']);
            $table->longText('description')->nullable();
            $table->string('orgid')->nullable();
            $table->string('postedby')->nullable();
            $table->string('updatedby')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_refund_policies');
    }
};