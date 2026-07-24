<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->uuid('current_fiscal_year_id')->nullable()->after('orgid');
            $table->foreign('current_fiscal_year_id')
                ->references('id')->on('fiscal_years')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['current_fiscal_year_id']);
            $table->dropColumn('current_fiscal_year_id');
        });
    }
};