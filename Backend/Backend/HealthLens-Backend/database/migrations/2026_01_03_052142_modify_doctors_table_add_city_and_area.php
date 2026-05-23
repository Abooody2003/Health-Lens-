<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            // Add city column before address
            $table->string('city', 100)->nullable()->after('specialization_id');
            
            // Add area column before address
            $table->string('area', 100)->nullable()->after('city');
            
            // Make address nullable (it's now optional)
            $table->string('address', 500)->nullable()->change();
        });

        // Migrate existing data: parse "City, Area" format from address
        DB::table('doctors')->get()->each(function ($doctor) {
            if ($doctor->address) {
                $parts = explode(',', $doctor->address, 2);
                $city = trim($parts[0] ?? '');
                $area = trim($parts[1] ?? '');
                
                // If area is empty, try to extract from common patterns
                if (empty($area) && !empty($city)) {
                    // Check if city contains area info (e.g., "Damascus Midan")
                    if (strpos($city, ' ') !== false) {
                        $cityParts = explode(' ', $city, 2);
                        $city = trim($cityParts[0]);
                        $area = trim($cityParts[1] ?? '');
                    }
                }
                
                DB::table('doctors')
                    ->where('id', $doctor->id)
                    ->update([
                        'city' => $city ?: null,
                        'area' => $area ?: null,
                        'address' => null, // Clear old address or keep it? Let's keep it for now
                    ]);
            }
        });

        // Add indexes for filtering
        Schema::table('doctors', function (Blueprint $table) {
            $table->index('city');
            $table->index('area');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['city']);
            $table->dropIndex(['area']);
            
            // Drop columns
            $table->dropColumn(['city', 'area']);
            
            // Make address required again (if needed)
            $table->string('address', 500)->nullable(false)->change();
        });
    }
};
