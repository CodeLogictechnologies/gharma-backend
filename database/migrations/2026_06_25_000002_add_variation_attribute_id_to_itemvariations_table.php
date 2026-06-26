<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('itemvariations', function (Blueprint $table) {
            $table->uuid('variation_attribute_id')->nullable()->after('attribute');
            $table->foreign('variation_attribute_id')
                ->references('id')
                ->on('variation_attributes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('itemvariations', function (Blueprint $table) {
            $table->dropForeign(['variation_attribute_id']);
            $table->dropColumn('variation_attribute_id');
        });
    }
};
