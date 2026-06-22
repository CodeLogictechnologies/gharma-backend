<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('sub_categories', function (Blueprint $table) {
        $table->char('category_id', 36)->nullable()->after('orgid');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('sub_categories', function (Blueprint $table) {
        $table->dropColumn('category_id');
    });
}
};
