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
            ['id' => Str::uuid(), 'group_name' => 'order_status', 'value' => 'Pending',   'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'order_status', 'value' => 'Confirmed', 'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'order_status', 'value' => 'Packed',    'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'order_status', 'value' => 'Shipped',   'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'order_status', 'value' => 'Delivered', 'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'order_status', 'value' => 'Cancelled', 'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'order_status', 'value' => 'Returned',  'created_at' => $now, 'updated_at' => $now],
            ['id' => Str::uuid(), 'group_name' => 'order_status', 'value' => 'Refunded',  'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        DB::table('enum_types')->where('group_name', 'order_status')->delete();
    }
};