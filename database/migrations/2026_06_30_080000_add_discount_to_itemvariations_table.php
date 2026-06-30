<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('itemvariations', function (Blueprint $table) {
            $table->decimal('discount', 5, 2)->nullable()->after('threshold');
        });

        // Retailer-facing discount percentage must be between 0 and 100 (or null)
        DB::statement("
            ALTER TABLE itemvariations
            ADD CONSTRAINT itemvariations_discount_range
            CHECK (discount IS NULL OR (discount >= 0 AND discount <= 100))
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE itemvariations DROP CONSTRAINT IF EXISTS itemvariations_discount_range");

        Schema::table('itemvariations', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }
};
