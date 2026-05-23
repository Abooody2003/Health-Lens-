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
        Schema::table('surgery_reports', function (Blueprint $table) {
            // Make ablation_depth_um optional to match current AI capabilities
            $table->integer('ablation_depth_um')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surgery_reports', function (Blueprint $table) {
            // Revert to NOT NULL if needed
            $table->integer('ablation_depth_um')->nullable(false)->change();
        });
    }
};


