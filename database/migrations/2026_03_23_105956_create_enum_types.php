<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE TYPE user_status_enum AS ENUM('Pending', 'Approve', 'Reject')");
    }

    public function down(): void
    {
        DB::statement("DROP TYPE IF EXISTS user_status_enum");
    }
};