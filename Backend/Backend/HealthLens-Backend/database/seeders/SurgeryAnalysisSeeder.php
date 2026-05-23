<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SurgeryAnalysis;
use App\Models\User;
use Carbon\Carbon;

class SurgeryAnalysisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'user')->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Run UserSeeder first.');
            return;
        }

        $statuses = ['pending', 'processing', 'completed', 'failed'];
        $genders = ['male', 'female'];
        
        // Surgery types
        $surgeryTypes = [
            'LASIK',
            'PRK',
            'SMILE',
            'LASEK',
            'ICL',
            'RLE',
            'CXL',
            'PTK',
        ];

        $totalAnalyses = 450;
        $analyses = [];

        // Create analyses with realistic distribution
        // 60% completed, 20% pending, 15% processing, 5% failed
        $statusDistribution = [
            'completed' => (int)($totalAnalyses * 0.60),
            'pending' => (int)($totalAnalyses * 0.20),
            'processing' => (int)($totalAnalyses * 0.15),
            'failed' => (int)($totalAnalyses * 0.05),
        ];

        $createdCount = 0;
        $startDate = Carbon::now()->subMonths(6); // Last 6 months

        foreach ($statusDistribution as $status => $count) {
            for ($i = 0; $i < $count && $createdCount < $totalAnalyses; $i++) {
                $user = $users->random();
                $gender = $genders[array_rand($genders)];
                $age = rand(18, 65);
                
                // Realistic clinical parameters
                $kmax = round(rand(4000, 5500) / 100, 2); // 40.00 to 55.00 D
                $cct = rand(480, 580); // Central corneal thickness in microns
                $astigValue = round(rand(0, 400) / 100, 2); // 0.00 to 4.00 D
                
                // AI results (only for completed/processing)
                $kcProbability = null;
                $recommendedSurgery = null;
                $rsbUm = null;
                $ablationDepthUm = null;
                $safetyWarnings = null;
                
                if ($status === 'completed' || $status === 'processing') {
                    $kcProbability = round(rand(0, 10000) / 10000, 4); // 0.0000 to 1.0000
                    $recommendedSurgery = $surgeryTypes[array_rand($surgeryTypes)];
                    
                    if ($status === 'completed') {
                        $rsbUm = round(rand(250, 450) / 10, 2); // 25.0 to 45.0 microns
                        $ablationDepthUm = round(rand(5000, 15000) / 10, 2); // 50.0 to 150.0 microns
                        
                        // Safety warnings (some analyses have warnings)
                        if (rand(1, 10) <= 3) { // 30% have warnings
                            $warnings = [];
                            if (rand(1, 2) === 1) {
                                $warnings[] = 'Thin cornea detected';
                            }
                            if (rand(1, 3) === 1) {
                                $warnings[] = 'High astigmatism';
                            }
                            if (rand(1, 4) === 1) {
                                $warnings[] = 'Moderate keratoconus risk';
                            }
                            if (!empty($warnings)) {
                                $safetyWarnings = $warnings;
                            }
                        }
                    }
                }
                
                // Random date within last 6 months
                $randomDays = rand(0, 180);
                $createdAt = $startDate->copy()->addDays($randomDays);
                $updatedAt = $createdAt->copy()->addHours(rand(1, 72));
                
                $analyses[] = [
                    'user_id' => $user->id,
                    'age' => $age,
                    'gender' => $gender,
                    'kmax' => $kmax,
                    'cct' => $cct,
                    'astig_value' => $astigValue,
                    'kc_probability' => $kcProbability,
                    'recommended_surgery' => $recommendedSurgery,
                    'rsb_um' => $rsbUm,
                    'ablation_depth_um' => $ablationDepthUm,
                    'safety_warnings' => $safetyWarnings ? json_encode($safetyWarnings) : null,
                    'status' => $status,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ];
                
                $createdCount++;
            }
        }

        // Insert in batches for better performance
        $chunks = array_chunk($analyses, 100);
        foreach ($chunks as $chunk) {
            SurgeryAnalysis::insert($chunk);
        }

        $this->command->info("Created {$createdCount} surgery analyses with realistic data!");
        $this->command->info("Status distribution:");
        foreach ($statusDistribution as $status => $count) {
            $this->command->info("  - {$status}: {$count}");
        }
    }
}
