<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variation_attributes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('orgid');
            $table->foreign('orgid')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
            $table->string('name');
            $table->enum('status', ['Y', 'N'])->default('Y');
            $table->string('postedby')->nullable();
            $table->string('updatedby')->nullable();
            $table->timestamps();
        });

        // Partial unique index — only active rows (status = Y) enforce uniqueness per org.
        // Soft-deleted rows do not block re-creation of the same attribute name.
        DB::statement("
            CREATE UNIQUE INDEX variation_attributes_orgid_name_unique
            ON variation_attributes (orgid, name)
            WHERE status = 'Y'
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('variation_attributes');
    }
};
