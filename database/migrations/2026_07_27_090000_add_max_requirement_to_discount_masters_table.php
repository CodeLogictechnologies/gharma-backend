<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discount_masters', function (Blueprint $table) {
            $table->decimal('max_value', 10, 2)->nullable()->after('min_value');
        });
    }

    public function down(): void
    {
        Schema::table('discount_masters', function (Blueprint $table) {
            $table->dropColumn('max_value');
        });
    }
};
