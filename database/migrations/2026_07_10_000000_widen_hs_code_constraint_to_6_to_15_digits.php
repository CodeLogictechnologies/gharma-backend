<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE items DROP CONSTRAINT IF EXISTS items_hs_code_digits");
        DB::statement("ALTER TABLE itemvariations DROP CONSTRAINT IF EXISTS itemvariations_hs_code_digits");

        // Clear any existing values that don't fit the new 6-15 digit rule
        DB::table('items')->whereRaw("hs_code IS NOT NULL AND hs_code !~ '^[0-9]{6,15}$'")->update(['hs_code' => null]);
        DB::table('itemvariations')->whereRaw("hs_code IS NOT NULL AND hs_code !~ '^[0-9]{6,15}$'")->update(['hs_code' => null]);

        DB::statement("ALTER TABLE items ADD CONSTRAINT items_hs_code_digits CHECK (hs_code IS NULL OR hs_code ~ '^[0-9]{6,15}$')");
        DB::statement("ALTER TABLE itemvariations ADD CONSTRAINT itemvariations_hs_code_digits CHECK (hs_code IS NULL OR hs_code ~ '^[0-9]{6,15}$')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE items DROP CONSTRAINT IF EXISTS items_hs_code_digits");
        DB::statement("ALTER TABLE itemvariations DROP CONSTRAINT IF EXISTS itemvariations_hs_code_digits");

        DB::statement("ALTER TABLE items ADD CONSTRAINT items_hs_code_digits CHECK (hs_code IS NULL OR hs_code ~ '^[0-9]{8,15}$')");
        DB::statement("ALTER TABLE itemvariations ADD CONSTRAINT itemvariations_hs_code_digits CHECK (hs_code IS NULL OR hs_code ~ '^[0-9]{8,15}$')");
    }
};
