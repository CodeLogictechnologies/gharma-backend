<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->uuid('parent_id')->nullable()->after('image');
            $table->unsignedTinyInteger('level')->default(0)->after('parent_id');

            $table->foreign('parent_id')
                  ->references('id')->on('categories')
                  ->nullOnDelete();

            $table->index(['parent_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['parent_id', 'level']);
            $table->dropColumn(['parent_id', 'level']);
        });
    }
};