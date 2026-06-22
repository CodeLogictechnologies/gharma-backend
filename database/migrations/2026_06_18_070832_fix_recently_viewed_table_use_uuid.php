<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The original create_recently_viewed_table migration used
     * unsignedBigInteger for variation_id / org_id / user_id. But in this
     * app, itemvariations.id, orgid, and userid are all UUID strings
     * (char(36)) — not auto-increment integers. Inserting a UUID into a
     * bigint column fails at the DB level (that was the "Database error
     * occurred." you were getting from /recently-viewed/save).
     *
     * This migration drops the existing recently_viewed table and
     * recreates it with uuid columns instead.
     */
    public function up(): void
    {
        Schema::dropIfExists('recently_viewed');

        Schema::create('recently_viewed', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Comes from the route's :variationid param (matches itemvariations.id, uuid)
            $table->uuid('variation_id');

            // Comes from JWTAuth::parseToken()->getPayload()->get('profile')['orgid'] (uuid)
            $table->uuid('org_id');

            // Comes from JWTAuth::parseToken()->getPayload()->get('profile')['userid'] (uuid)
            // (i.e. the user who is currently logged in / who owns the token)
            $table->uuid('user_id');

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