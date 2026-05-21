<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_tabs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tab_name');
            $table->string('status')->default('Y');
            $table->uuid('orgid')->nullable();
            $table->uuid('postedby')->nullable();
            $table->uuid('updatedby')->nullable();
            $table->timestamps();
        });

        Schema::create('home_tab_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('home_tab_id');
            $table->uuid('category_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_tab_categories');
        Schema::dropIfExists('home_tabs');
    }
};