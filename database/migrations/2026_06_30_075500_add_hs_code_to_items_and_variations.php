<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('hs_code', 6)->nullable()->after('company_product_code');
        });

        Schema::table('itemvariations', function (Blueprint $table) {
            $table->string('hs_code', 6)->nullable()->after('company_product_code');
        });

        // PostgreSQL CHECK constraint: hs_code must be exactly 6 digits (or null)
        DB::statement("
            ALTER TABLE items
            ADD CONSTRAINT items_hs_code_6digits
            CHECK (hs_code IS NULL OR hs_code ~ '^[0-9]{6}$')
        ");

        DB::statement("
            ALTER TABLE itemvariations
            ADD CONSTRAINT itemvariations_hs_code_6digits
            CHECK (hs_code IS NULL OR hs_code ~ '^[0-9]{6}$')
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE items DROP CONSTRAINT IF EXISTS items_hs_code_6digits");
        DB::statement("ALTER TABLE itemvariations DROP CONSTRAINT IF EXISTS itemvariations_hs_code_6digits");

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('hs_code');
        });

        Schema::table('itemvariations', function (Blueprint $table) {
            $table->dropColumn('hs_code');
        });
    }
};