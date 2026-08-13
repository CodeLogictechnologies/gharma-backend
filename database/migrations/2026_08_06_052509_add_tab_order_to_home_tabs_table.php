<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_tabs', function (Blueprint $table) {
            $table->integer('tab_order')->default(0)->after('tab_name');
        });
    }

    public function down(): void
    {
        Schema::table('home_tabs', function (Blueprint $table) {
            $table->dropColumn('tab_order');
        });
    }
};