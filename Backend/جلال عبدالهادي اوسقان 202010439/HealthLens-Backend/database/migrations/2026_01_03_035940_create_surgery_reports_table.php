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
        Schema::create('surgery_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Input data (clinical parameters)
            $table->integer('age')->nullable();
            $table->string('gender', 10)->nullable();
            $table->decimal('kmax', 8, 2)->nullable();
            $table->integer('cct')->nullable();
            $table->decimal('astigmatism', 8, 2)->nullable();

            // AI outputs (required)
            $table->decimal('kc_probability', 6, 4);
            $table->string('recommended_surgery');
            $table->integer('rsb_um');
            $table->integer('ablation_depth_um');

            // Optional extras
            $table->json('warnings')->nullable();
            $table->string('eye')->nullable(); // Right / Left

            $table->timestamps();

            // Helpful indexes
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surgery_reports');
    }
};
