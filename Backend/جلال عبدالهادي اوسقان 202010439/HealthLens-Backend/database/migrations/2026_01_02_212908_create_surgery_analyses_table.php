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
        Schema::create('surgery_analyses', function (Blueprint $table) {
            $table->id();
    
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
    
            $table->unsignedInteger('age');
    
            $table->enum('gender', ['male', 'female']);
    
            // Clinical parameters
            $table->decimal('kmax', 8, 2);
            $table->decimal('cct', 8, 2);
            $table->decimal('astig_value', 8, 2);
    
            // AI results (nullable until processed)
            $table->decimal('kc_probability', 6, 4)->nullable();
            $table->string('recommended_surgery')->nullable();
            $table->decimal('rsb_um', 8, 2)->nullable();
            $table->decimal('ablation_depth_um', 8, 2)->nullable();
    
            $table->json('safety_warnings')->nullable();
    
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])
                  ->default('pending');
    
            $table->timestamps();
    
            // Helpful indexes
            $table->index('user_id');
            $table->index('status');
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
        Schema::dropIfExists('surgery_analyses');
    }
    
};
