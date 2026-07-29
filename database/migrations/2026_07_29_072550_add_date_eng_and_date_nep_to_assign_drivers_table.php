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
        Schema::table('assign_drivers', function (Blueprint $table) {
            $table->string('date_eng')->nullable()->after('delivery_date');
            $table->string('date_nep')->nullable()->after('date_eng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assign_drivers', function (Blueprint $table) {
            $table->dropColumn(['date_eng', 'date_nep']);
        });
    }
};
