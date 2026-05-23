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
        Schema::create('specializations', function (Blueprint $table) {
            $table->id();
    
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
    
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
    
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
    
            $table->timestamps();
    
            // Helpful indexes
            $table->index('is_active');
            $table->index('display_order');
        });
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('specializations');
    }
    
};
