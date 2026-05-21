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
        Schema::table('wholesaler_prices', function (Blueprint $table) {
            $table->string('postedby', 36)->nullable()->change();
            $table->string('updatedby', 36)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('wholesaler_prices', function (Blueprint $table) {
            $table->string('postedby', 36)->nullable(false)->change();
            $table->string('updatedby', 36)->nullable(false)->change();
        });
    }
};
