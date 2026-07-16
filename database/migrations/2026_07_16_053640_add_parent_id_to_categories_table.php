<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->uuid('sub_category_id')->nullable()->after('image');
            $table->unsignedTinyInteger('level')->default(1)->after('sub_category_id');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->uuid(['sub_category_id']);
        });
    }
};