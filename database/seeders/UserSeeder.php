<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user (only if doesn't exist)
        User::firstOrCreate(
            ['email' => 'admin@institut-paramedical-sba.dz'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'locale' => 'fr',
                'must_change_password' => false,
                'is_active' => true,
            ]
        );

        // Create sample students (only if they don't exist)
        $students = [
            [
                'email' => 'ahmed.benali@student.institut.dz',
                'name' => 'Ahmed Benali',
                'password' => Hash::make('student123'),
                'role' => 'student',
                'locale' => 'ar',
                'must_change_password' => true,
                'is_active' => true,
            ],
            [
                'email' => 'fatima.zohra@student.institut.dz',
                'name' => 'Fatima Zohra',
                'password' => Hash::make('student123'),
                'role' => 'student',
                'locale' => 'fr',
                'must_change_password' => true,
                'is_active' => true,
            ],
            [
                'email' => 'mohamed.amine@student.institut.dz',
                'name' => 'Mohamed Amine',
                'password' => Hash::make('student123'),
                'role' => 'student',
                'locale' => 'fr',
                'must_change_password' => true,
                'is_active' => true,
            ],
        ];

        foreach ($students as $student) {
            User::firstOrCreate(
                ['email' => $student['email']],
                $student
            );
        }
    }
}
