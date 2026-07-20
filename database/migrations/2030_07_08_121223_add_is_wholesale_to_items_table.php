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
        try {
            Schema::table('items', function (Blueprint $table) {
                $table->enum('is_wholesale', ['Y', 'N'])->nullable()->after('excise_value');
            });
        } catch (\Throwable $e) {
            // Column already exists — safe to ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('is_wholesale');
            });
        } catch (\Throwable $e) {
            // Column doesn't exist — safe to ignore
        }
    }
};