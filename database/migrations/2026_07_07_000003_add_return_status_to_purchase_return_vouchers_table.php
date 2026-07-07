<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_return_vouchers', function (Blueprint $table) {
            $table->enum('return_status', ['Pending', 'Approved', 'Rejected'])
                ->default('Pending')
                ->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_return_vouchers', function (Blueprint $table) {
            $table->dropColumn('return_status');
        });
    }
};
