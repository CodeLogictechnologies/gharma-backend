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
            ['id' => Str::uuid(), 'group_name' => 'esewa_status', 'value' => 'BOOKED',   'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'esewa_status', 'value' => 'SUCCESS',  'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'esewa_status', 'value' => 'PENDING',  'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'esewa_status', 'value' => 'FAILED',   'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'esewa_status', 'value' => 'CANCELED', 'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'esewa_status', 'value' => 'REVERTED', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        DB::table('enum_types')->where('group_name', 'esewa_status')->delete();
    }
};