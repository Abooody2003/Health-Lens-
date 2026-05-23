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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
    
            $table->string('name');
    
            $table->foreignId('specialization_id')
                  ->constrained('specializations')
                  ->restrictOnDelete();
    
            $table->string('address', 500);
            $table->string('phone_number', 20);
            $table->string('email')->nullable();
    
            $table->boolean('is_active')->default(true);
    
            $table->timestamps();
    
            // Helpful indexes
            $table->index('specialization_id');
            $table->index('is_active');
        });
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('doctors');
    }
    
};
