<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_images', function (Blueprint $table) {
            $table->unsignedInteger('order_number')->default(0)->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('item_images', function (Blueprint $table) {
            $table->dropColumn('order_number');
        });
    }
};