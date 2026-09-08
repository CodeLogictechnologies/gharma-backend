<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_masters', function (Blueprint $table) {
            // PostgreSQL: Laravel's enum() compiles to a CHECK constraint, works fine on pgsql.
            $table->enum('bill_type', ['vat', 'abbreviated'])
                ->default('vat')
                ->after('voucher_number');
        });
    }

    public function down(): void
    {
        Schema::table('order_masters', function (Blueprint $table) {
            $table->dropColumn('bill_type');
        });
    }
};