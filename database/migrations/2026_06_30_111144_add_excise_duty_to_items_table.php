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
        Schema::table('items', function (Blueprint $table) {
            $table->enum('excise_status', ['Y', 'N'])->default('N')->after('vat_status');
            $table->enum('excise_type', ['percentage', 'fixed'])->nullable()->after('excise_status');
            $table->decimal('excise_percentage', 5, 2)->nullable()->after('excise_type');
            $table->decimal('excise_value', 8, 2)->nullable()->after('excise_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['excise_status', 'excise_type', 'excise_percentage', 'excise_value']);
        });
    }
};
