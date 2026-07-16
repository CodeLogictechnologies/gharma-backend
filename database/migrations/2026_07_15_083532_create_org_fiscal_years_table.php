<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('org_fiscal_years', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('fiscal_year_id');
            $table->uuid('org_id');
            $table->string('is_current', 1)->default('N'); // Y/N
            $table->string('status', 1)->default('Y');      // Y/N
            $table->timestamps();

            // If org_id doesn't exist in organizations table, insert fails (stops)
            $table->foreign('org_id')
                  ->references('id')->on('organizations')
                  ->onDelete('cascade');

            // If fiscal_year_id doesn't exist in fiscal_years table, insert fails (stops)
            $table->foreign('fiscal_year_id')
                  ->references('id')->on('fiscal_years')
                  ->onDelete('cascade');

            $table->unique(['org_id', 'fiscal_year_id']); // one row per org per fiscal year
        });

        DB::statement("ALTER TABLE org_fiscal_years ADD CONSTRAINT chk_org_fiscal_years_is_current CHECK (is_current IN ('Y','N'))");
        DB::statement("ALTER TABLE org_fiscal_years ADD CONSTRAINT chk_org_fiscal_years_status CHECK (status IN ('Y','N'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('org_fiscal_years');
    }
};