<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── sub_category_items.subcategoryid → categories.id ──
        Schema::table('sub_category_items', function ($table) {
            $table->dropForeign('sub_category_items_subcategoryid_foreign');
        });

        Schema::table('sub_category_items', function ($table) {
            $table->foreign('subcategoryid')
                ->references('id')->on('categories')
                ->onDelete('cascade');
        });

        // ── sub_sub_category_items.subsubcategoryid → categories.id ──
        // Only run if that FK currently points somewhere else (e.g. sub_sub_categories).
        // Check your actual constraint name first with:
        // SELECT conname FROM pg_constraint WHERE conrelid = 'sub_sub_category_items'::regclass;
        if (Schema::hasTable('sub_sub_category_items')) {
            $constraints = DB::select("
                SELECT conname FROM pg_constraint
                WHERE conrelid = 'sub_sub_category_items'::regclass
                AND contype = 'f'
            ");

            foreach ($constraints as $c) {
                DB::statement("ALTER TABLE sub_sub_category_items DROP CONSTRAINT {$c->conname}");
            }

            Schema::table('sub_sub_category_items', function ($table) {
                $table->foreign('subsubcategoryid')
                    ->references('id')->on('categories')
                    ->onDelete('cascade');
                $table->foreign('itemid')
                    ->references('id')->on('items')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('sub_category_items', function ($table) {
            $table->dropForeign(['subcategoryid']);
        });
        Schema::table('sub_category_items', function ($table) {
            $table->foreign('subcategoryid')
                ->references('id')->on('sub_categories')
                ->onDelete('cascade');
        });
    }
};