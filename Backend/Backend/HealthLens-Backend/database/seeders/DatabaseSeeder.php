<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            SpecializationSeeder::class,
            AdminUserSeeder::class,
            DoctorSeeder::class,
            UserSeeder::class,
            SurgeryAnalysisSeeder::class,
        ]);
    }
}
