<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
    
            $table->foreignId('chat_id')
                  ->constrained('chats')
                  ->cascadeOnDelete();
    
            // Nullable when sender is AI
            $table->unsignedBigInteger('sender_id')->nullable();
    
            $table->enum('sender_type', ['user', 'ai']);
    
            $table->text('text')->nullable();
    
            $table->enum('type', ['text', 'image', 'file'])->default('text');
    
            $table->enum('status', ['sent', 'delivered', 'read'])->default('sent');
    
            $table->timestamps();
    
            // Helpful indexes
            $table->index('chat_id');
            $table->index(['sender_type', 'sender_id']);
            $table->index('created_at');
        });
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
    
};
