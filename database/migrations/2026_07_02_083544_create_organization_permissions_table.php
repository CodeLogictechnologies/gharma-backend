<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_permissions', function (Blueprint $table) {
            $table->uuid('org_id');
            $table->uuid('permission_id');

            $table->foreign('org_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();

            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->cascadeOnDelete();

            $table->primary(['org_id', 'permission_id'], 'organization_permissions_org_permission_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_permissions');
    }
};