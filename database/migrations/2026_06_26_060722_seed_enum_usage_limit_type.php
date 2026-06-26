<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        DB::table('enum_types')->insert([
            ['id' => Str::uuid(), 'group_name' => 'usage_limit_type', 'value' => 'once',     'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'usage_limit_type', 'value' => 'limited',  'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'usage_limit_type', 'value' => 'per_user', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        DB::table('enum_types')->where('group_name', 'usage_limit_type')->delete();
    }
};