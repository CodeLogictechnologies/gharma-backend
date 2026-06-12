<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only add column if it doesn't already exist
        if (!Schema::hasColumn('discounts', 'coupon_code')) {
            Schema::table('discounts', function (Blueprint $table) {
                $table->string('coupon_code')->nullable()->after('percentage');
            });
        }

        // Extend the type enum to include 'coupon'
        DB::statement("ALTER TABLE discounts MODIFY COLUMN type ENUM('percentage', 'fixed', 'coupon') NOT NULL");
    }

    public function down(): void
    {
        if (Schema::hasColumn('discounts', 'coupon_code')) {
            Schema::table('discounts', function (Blueprint $table) {
                $table->dropColumn('coupon_code');
            });
        }

        DB::statement("ALTER TABLE discounts MODIFY COLUMN type ENUM('percentage', 'fixed') NOT NULL");
    }
};