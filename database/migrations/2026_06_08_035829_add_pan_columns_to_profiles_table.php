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
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('pan_number')->nullable()->after('registration_number');
            $table->string('pan_image')->nullable()->after('pan_number');
            $table->string('registration_number_image')->nullable()->after('pan_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['pan_number', 'pan_image', 'registration_number_image']);
        });
    }
};
