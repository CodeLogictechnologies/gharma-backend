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
            ['id' => Str::uuid(), 'group_name' => 'refund_status', 'value' => 'PENDING',      'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'refund_status', 'value' => 'UNDER_REVIEW', 'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'refund_status', 'value' => 'APPROVED',     'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'refund_status', 'value' => 'REJECTED',     'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'refund_status', 'value' => 'PROCESSING',   'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'refund_status', 'value' => 'COMPLETED',    'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'refund_status', 'value' => 'CANCELLED',    'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        DB::table('enum_types')->where('group_name', 'refund_status')->delete();
    }
};