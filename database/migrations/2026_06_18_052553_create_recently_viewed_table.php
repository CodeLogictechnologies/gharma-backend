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
        Schema::create('recently_viewed', function (Blueprint $table) {
            $table->uuid();

            // Comes from the route's :variationid param (the item being viewed)
            $table->unsignedBigInteger('variation_id');

            // Comes from JWTAuth::parseToken()->getPayload()->get('profile')['orgid']
            $table->unsignedBigInteger('org_id');

            // Comes from JWTAuth::parseToken()->getPayload()->get('profile')['userid']
            // (i.e. the user who is currently logged in / who owns the token)
            $table->unsignedBigInteger('user_id');

            $table->timestamps();

            // Prevent the same user from logging the same variation twice;
            // instead a repeat view should just touch updated_at.
            $table->unique(['user_id', 'org_id', 'variation_id'], 'recently_viewed_unique');

            $table->index('user_id');
            $table->index('org_id');
            $table->index('variation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recently_viewed');
    }
};