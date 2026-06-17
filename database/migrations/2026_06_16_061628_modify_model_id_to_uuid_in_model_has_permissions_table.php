<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Drop existing foreign key if exists (any name)
        DB::statement("
            ALTER TABLE model_has_permissions
                DROP FOREIGN KEY IF EXISTS model_has_permissions_permission_id_foreign
        ");

        DB::statement("
            ALTER TABLE model_has_permissions
                DROP PRIMARY KEY,
                DROP INDEX IF EXISTS model_has_permissions_model_id_model_type_index,
                MODIFY COLUMN model_id CHAR(36) NOT NULL,
                ADD PRIMARY KEY (permission_id, model_id, model_type),
                ADD INDEX model_has_permissions_model_id_model_type_index (model_id, model_type),
                ADD CONSTRAINT mhp_permission_id_foreign
                    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
        ");

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::statement("
            ALTER TABLE model_has_permissions
                DROP FOREIGN KEY IF EXISTS mhp_permission_id_foreign
        ");

        DB::statement("
            ALTER TABLE model_has_permissions
                DROP PRIMARY KEY,
                DROP INDEX IF EXISTS model_has_permissions_model_id_model_type_index,
                MODIFY COLUMN model_id BIGINT UNSIGNED NOT NULL,
                ADD PRIMARY KEY (permission_id, model_id, model_type),
                ADD INDEX model_has_permissions_model_id_model_type_index (model_id, model_type),
                ADD CONSTRAINT model_has_permissions_permission_id_foreign
                    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
        ");

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};