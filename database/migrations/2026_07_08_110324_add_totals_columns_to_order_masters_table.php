<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->enum('excise_type', ['percentage', 'fixed'])->nullable()->after('is_wholesale');
            $table->decimal('excise_percent', 5, 2)->nullable()->after('excise_type');
            $table->decimal('excise_amount', 10, 2)->default(0)->after('excise_percent');
            $table->decimal('vat_percent', 5, 2)->default(0)->after('excise_amount');
            $table->decimal('vat_amount', 10, 2)->default(0)->after('vat_percent');
        });
    }

    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn([
                'excise_type',
                'excise_percent',
                'excise_amount',
                'vat_percent',
                'vat_amount',
            ]);
        });
    }
};