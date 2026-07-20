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
        if (!Schema::hasColumn('items', 'is_wholesale')) {
            Schema::table('items', function (Blueprint $table) {
                $table->enum('is_wholesale', ['Y', 'N'])->nullable()->after('excise_value');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('items', 'is_wholesale')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('is_wholesale');
            });
        }
    }
};