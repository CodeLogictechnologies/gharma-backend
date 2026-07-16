<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('itemvariations', function (Blueprint $table) {
            $table->uuid('unit_id')->nullable();
            $table->string('unit')->nullable();
        });
    }

    public function down()
    {
        Schema::table('itemvariations', function (Blueprint $table) {
            $table->dropColumn([
                'unit_id',
                'unit',
            ]);
        });
    }
};
