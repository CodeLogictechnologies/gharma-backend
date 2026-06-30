<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'tax_number',
                'registration_number',
                'registration_number_image',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('tax_number')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('registration_number_image')->nullable();
        });
    }
};