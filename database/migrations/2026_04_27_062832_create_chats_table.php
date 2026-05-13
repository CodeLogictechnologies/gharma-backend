<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('receiverid');
            $table->foreign('receiverid')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->uuid('senderid');
            $table->foreign('senderid')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->enum('sender_type', ['user', 'admin']);
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};