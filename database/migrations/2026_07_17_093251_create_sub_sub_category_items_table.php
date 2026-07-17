<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sub_sub_category_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('orgid')->nullable();
            $table->uuid('subsubcategoryid');
            $table->uuid('itemid');
            $table->char('status', 1)->default('Y');
            $table->uuid('postedby')->nullable();
            $table->uuid('updatedby')->nullable();
            $table->timestamps();

            $table->foreign('subsubcategoryid')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('itemid')->references('id')->on('items')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sub_sub_category_items');
    }
};