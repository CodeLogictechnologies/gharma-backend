<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->uuid('sub_category_id')->nullable()->after('image');
            });
        } catch (\Throwable $e) {
            // Column already exists — safe to ignore
        }

        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->unsignedTinyInteger('level')->default(1)->after('sub_category_id');
            });
        } catch (\Throwable $e) {
            // Column already exists — safe to ignore
        }
    }

    public function down(): void
    {
        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('level');
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('sub_category_id');
            });
        } catch (\Throwable $e) {
        }
    }
};