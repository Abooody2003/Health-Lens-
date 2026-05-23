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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
    
            // Polymorphic relation
            $table->unsignedBigInteger('mediable_id');
            $table->string('mediable_type');
    
            // Media metadata
            $table->string('type'); // chat_image, orbscan_anterior, avatar, etc.
            $table->string('file_path', 500);
            $table->string('file_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
    
            $table->timestamps();
    
            // Indexes for polymorphic lookups
            $table->index(['mediable_type', 'mediable_id']);
            $table->index('type');
        });
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media');
    }
    
};
