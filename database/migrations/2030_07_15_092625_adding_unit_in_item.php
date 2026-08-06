<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('itemvariations', function (Blueprint $table) {
            if (!Schema::hasColumn('itemvariations', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable(); // adjust type/constraints to match your original
            }
        });
    }

    public function down()
    {
        Schema::table('itemvariations', function (Blueprint $table) {
            if (Schema::hasColumn('itemvariations', 'unit_id')) {
                $table->dropColumn('unit_id');
            }
        });
    }
};
