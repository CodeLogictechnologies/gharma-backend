<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('pan_image')->nullable()->after('registration_number');
            $table->string('registration_number_image')->nullable()->after('pan_image');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (Schema::hasColumn('vendors', 'pan_image')) {
                $table->dropColumn('pan_image');
            }
            if (Schema::hasColumn('vendors', 'registration_number_image')) {
                $table->dropColumn('registration_number_image');
            }
        });
    }
};