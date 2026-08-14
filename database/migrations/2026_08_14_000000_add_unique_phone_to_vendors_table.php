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
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropUnique('vendors_email_unique');
            $table->unique(['orgid', 'email']);
            $table->unique(['orgid', 'phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropUnique(['orgid', 'email']);
            $table->dropUnique(['orgid', 'phone']);
            $table->unique('email');
        });
    }
};
