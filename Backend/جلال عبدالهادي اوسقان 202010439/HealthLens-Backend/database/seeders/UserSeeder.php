<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 180 regular users with realistic data
        User::factory(180)->create();

        // Create some premium users with specific plans
        $premiumUsers = [
            [
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'email' => 'sarah.johnson@example.com',
                'username' => 'sarah.johnson',
                'password' => Hash::make('password123'),
                'date_of_birth' => '1990-05-15',
                'gender' => 'female',
                'plan' => 'premium',
                'role' => 'user',
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Chen',
                'email' => 'michael.chen@example.com',
                'username' => 'michael.chen',
                'password' => Hash::make('password123'),
                'date_of_birth' => '1985-08-22',
                'gender' => 'male',
                'plan' => 'pro',
                'role' => 'user',
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Emma',
                'last_name' => 'Williams',
                'email' => 'emma.williams@example.com',
                'username' => 'emma.williams',
                'password' => Hash::make('password123'),
                'date_of_birth' => '1992-11-30',
                'gender' => 'female',
                'plan' => 'premium',
                'role' => 'user',
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Martinez',
                'email' => 'david.martinez@example.com',
                'username' => 'david.martinez',
                'password' => Hash::make('password123'),
                'date_of_birth' => '1988-03-10',
                'gender' => 'male',
                'plan' => 'pro',
                'role' => 'user',
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Olivia',
                'last_name' => 'Brown',
                'email' => 'olivia.brown@example.com',
                'username' => 'olivia.brown',
                'password' => Hash::make('password123'),
                'date_of_birth' => '1995-07-18',
                'gender' => 'female',
                'plan' => 'premium',
                'role' => 'user',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($premiumUsers as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }

        $this->command->info('Created 180+ users with realistic data!');
    }
}
