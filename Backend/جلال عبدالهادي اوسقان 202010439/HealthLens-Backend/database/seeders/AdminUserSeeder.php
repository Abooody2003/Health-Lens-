<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@healthlens.test'],
            [
                'first_name' => 'Admin',
                'last_name'  => 'HealthLens',
                'username'   => 'admin',
                'password'   => Hash::make('admin123'),
                'role'       => 'admin',
            ]
        );
    }
}
