<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;
use App\Models\Specialization;

class DoctorSeeder extends Seeder
{
    public function run()
    {
        $specializations = Specialization::all();
        
        if ($specializations->isEmpty()) {
            $this->command->warn('Specializations not found. Run SpecializationSeeder first.');
            return;
        }

        // Syrian cities and areas for realistic data
        $cities = ['Damascus', 'Aleppo', 'Homs', 'Latakia', 'Tartus', 'Hama', 'Daraa', 'As-Suwayda'];
        $areas = [
            'Damascus' => ['Midan', 'Mazzeh', 'Abu Rummaneh', 'Kafr Sousa', 'Mezzeh', 'Barzeh', 'Dummar', 'Jaramana'],
            'Aleppo' => ['Al-Aziziyah', 'Al-Sabil', 'Al-Jamiliyah', 'Al-Shaar', 'Al-Masharqa', 'Al-Hamadaniyah'],
            'Homs' => ['Al-Waer', 'Al-Ghouta', 'Al-Hamra', 'Al-Dablan', 'Al-Khaldiyah'],
            'Latakia' => ['Al-Aziziyah', 'Al-Sinaa', 'Al-Raml', 'Al-Salibiyah'],
            'Tartus' => ['Al-Mina', 'Al-Qalaa', 'Al-Sahel'],
            'Hama' => ['Al-Hamidiyah', 'Al-Souk', 'Al-Mashariq'],
            'Daraa' => ['Al-Manshiyah', 'Al-Balad', 'Al-Sad'],
            'As-Suwayda' => ['Al-Mashariq', 'Al-Balad', 'Al-Sahel'],
        ];

        $firstNames = [
            'Ahmad', 'Mohammed', 'Ali', 'Hassan', 'Omar', 'Khaled', 'Youssef', 'Tarek', 'Bassam', 'Fadi',
            'Lina', 'Rania', 'Nour', 'Sara', 'Hala', 'Maya', 'Dina', 'Rana', 'Layla', 'Nada'
        ];
        
        $lastNames = [
            'Al-Khatib', 'Haddad', 'Saleh', 'Nasser', 'Hamdan', 'Al-Mahmoud', 'Al-Ahmad', 'Al-Hassan',
            'Al-Ali', 'Al-Omar', 'Al-Khalil', 'Al-Rashid', 'Al-Farouk', 'Al-Zahra', 'Al-Mansour'
        ];

        $doctors = [];
        $doctorCount = 0;
        $maxDoctors = 45;

        // Ensure we have doctors for each specialization
        foreach ($specializations as $specialization) {
            $doctorsPerSpecialization = rand(3, 8);
            
            for ($i = 0; $i < $doctorsPerSpecialization && $doctorCount < $maxDoctors; $i++) {
                $city = $cities[array_rand($cities)];
                $area = $areas[$city][array_rand($areas[$city])];
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $name = "Dr. {$firstName} {$lastName}";
                
                // Generate phone number (Syrian format)
                $phoneNumber = '09' . rand(10000000, 99999999);
                
                // Generate email
                $email = strtolower(str_replace(' ', '.', $firstName . '.' . $lastName)) . '@healthlens.sy';
                
                // Generate address
                $address = "{$area}, {$city}, Syria";
                
                $doctors[] = [
                    'name' => $name,
                    'specialization_id' => $specialization->id,
                    'city' => $city,
                    'area' => $area,
                    'address' => $address,
                    'phone_number' => $phoneNumber,
                    'email' => $email,
                    'is_active' => rand(0, 10) > 1, // 90% active
                ];
                
                $doctorCount++;
            }
        }

        // Add some additional doctors to reach target
        while ($doctorCount < $maxDoctors) {
            $city = $cities[array_rand($cities)];
            $area = $areas[$city][array_rand($areas[$city])];
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $name = "Dr. {$firstName} {$lastName}";
            $specialization = $specializations->random();
            
            $phoneNumber = '09' . rand(10000000, 99999999);
            $email = strtolower(str_replace(' ', '.', $firstName . '.' . $lastName)) . rand(1, 99) . '@healthlens.sy';
            $address = "{$area}, {$city}, Syria";
            
            $doctors[] = [
                'name' => $name,
                'specialization_id' => $specialization->id,
                'city' => $city,
                'area' => $area,
                'address' => $address,
                'phone_number' => $phoneNumber,
                'email' => $email,
                'is_active' => rand(0, 10) > 1,
            ];
            
            $doctorCount++;
        }

        foreach ($doctors as $doctor) {
            Doctor::updateOrCreate(
                [
                    'name' => $doctor['name'],
                    'specialization_id' => $doctor['specialization_id'],
                ],
                $doctor
            );
        }

        $this->command->info("Created {$doctorCount} doctors across all specializations!");
    }
}
