<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('location_trackers', function (Blueprint $table) {

            // Step 1: Add column first (nullable to avoid FK error)
            $table->uuid('orderid')->nullable()->after('orgid');

            // Step 2: Add foreign key
            $table->foreign('orderid')
                ->references('id')
                ->on('order_masters')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('location_trackers', function (Blueprint $table) {

            // Drop foreign key first
            $table->dropForeign(['orderid']);

            // Then drop column
            $table->dropColumn('orderid');
        });
    }
};