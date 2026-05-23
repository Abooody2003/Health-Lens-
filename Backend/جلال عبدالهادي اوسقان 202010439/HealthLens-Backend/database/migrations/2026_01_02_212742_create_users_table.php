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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
    
            $table->string('first_name', 100);
            $table->string('last_name', 100);
    
            $table->string('email')->unique();
            $table->string('username', 50)->unique();
    
            $table->string('password');
    
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
    
            $table->enum('plan', ['free', 'premium', 'pro'])->default('free');
    
            $table->string('avatar')->nullable();
    
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
    
            $table->timestamps();
    
            // Helpful indexes
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
        Schema::dropIfExists('users');
    }
    
};
