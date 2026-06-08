<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_tabs', function (Blueprint $table) {
            $table->string('icon_name', 100)->nullable()->after('tab_name');
            $table->string('bg_color', 7)->nullable()->after('icon_name');
        });
    }

    public function down(): void
    {
        Schema::table('home_tabs', function (Blueprint $table) {
            $table->dropColumn(['icon_name', 'bg_color']);
        });
    }
};