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
            ['id' => Str::uuid(), 'group_name' => 'active', 'value' => 'active',   'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'active', 'value' => 'inactive', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        DB::table('enum_types')->where('group_name', 'active')->delete();
    }
};