<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discount_masters', function (Blueprint $table) {
            $table->string('starts_time', 5)->default('00:00')->after('end_date_bs');
            $table->string('ends_time', 5)->default('23:59')->after('starts_time');
        });
    }

    public function down(): void
    {
        Schema::table('discount_masters', function (Blueprint $table) {
            $table->dropColumn(['starts_time', 'ends_time']);
        });
    }
};