<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('terms_privacy_policies', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'terms' | 'privacy'
            $table->longText('description')->nullable();
            $table->uuid('orgid');
            $table->uuid('postedby')->nullable();
            $table->uuid('updatedby')->nullable();
            $table->timestamps();

            $table->unique(['orgid', 'type']); // one row per type per org
        });
    }

    public function down()
    {
        Schema::dropIfExists('terms_privacy_policies');
    }
};