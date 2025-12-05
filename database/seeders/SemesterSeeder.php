<?php

namespace Database\Seeders;

use App\Models\Year;
use App\Models\Semester;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates 2 semesters (S1 and S2) for each year
     * Sets default dates for academic year 2024-2025
     */
    public function run(): void
    {
        $years = Year::all();
        $academicYear = '2024-2025'; // Current academic year

        foreach ($years as $year) {
            // Semester 1 (S1) - Usually September to January
            Semester::firstOrCreate(
                [
                    'year_id' => $year->id,
                    'semester_number' => 1,
                ],
                [
                    'name' => [
                        'fr' => 'Semestre 1',
                        'ar' => 'الفصل الأول',
                    ],
                    'start_date' => Carbon::create(2024, 9, 1), // September 1, 2024
                    'end_date' => Carbon::create(2025, 1, 31), // January 31, 2025
                    'academic_year' => $academicYear,
                    'is_active' => true,
                ]
            );

            // Semester 2 (S2) - Usually February to June
            Semester::firstOrCreate(
                [
                    'year_id' => $year->id,
                    'semester_number' => 2,
                ],
                [
                    'name' => [
                        'fr' => 'Semestre 2',
                        'ar' => 'الفصل الثاني',
                    ],
                    'start_date' => Carbon::create(2025, 2, 1), // February 1, 2025
                    'end_date' => Carbon::create(2025, 6, 30), // June 30, 2025
                    'academic_year' => $academicYear,
                    'is_active' => true,
                ]
            );
        }
    }
}

