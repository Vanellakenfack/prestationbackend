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
      // database/migrations/xxxx_create_messages_table.php
Schema::create('messages', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
    $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
    $table->text('content');
    $table->timestamp('read_at')->nullable();
    $table->timestamps();
    
    $table->index(['conversation_id', 'created_at']);
});

Schema::create('conversations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user1_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('user2_id')->constrained('users')->cascadeOnDelete();
    $table->timestamp('last_message_at')->nullable();
    $table->timestamps();
    
    $table->unique(['user1_id', 'user2_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
