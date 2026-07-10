<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('itemvariations', function (Blueprint $table) {
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable()->after('threshold');
            $table->decimal('discount_amount', 8, 2)->nullable()->after('discount');
        });

        // Existing rows already carry a percentage discount using them accordingly.
        DB::table('itemvariations')
            ->whereNotNull('discount')
            ->update(['discount_type' => 'percentage']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itemvariations', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_amount']);
        });
    }
};
