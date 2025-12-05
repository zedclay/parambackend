<?php

/**
 * Quick Example: How to Add Filière, Speciality, Years, and Semesters
 * 
 * This is a reference script showing the exact code to add academic structure.
 * You can copy this code into Laravel Tinker or create a custom seeder.
 * 
 * Usage:
 * 1. Copy the code below
 * 2. Run: php artisan tinker
 * 3. Paste and execute
 */

use App\Models\Filiere;
use App\Models\Speciality;
use App\Models\Year;
use App\Models\Semester;
use Carbon\Carbon;

// ============================================
// STEP 1: Create a Filière
// ============================================

$filiere = Filiere::create([
    'name' => [
        'fr' => 'Soins Infirmiers',
        'ar' => 'التمريض',
    ],
    'slug' => 'soins-infirmiers',
    'description' => [
        'fr' => 'Formation en soins infirmiers pour devenir infirmier/infirmière diplômé(e) d\'État de santé publique.',
        'ar' => 'تدريب في التمريض لتصبح ممرضًا / ممرضة معتمدًا في الصحة العامة.',
    ],
    'order' => 1,
    'is_active' => true,
]);

echo "✅ Filière created: {$filiere->name['fr']}\n";

// ============================================
// STEP 2: Create a Speciality
// ============================================

$speciality = Speciality::create([
    'filiere_id' => $filiere->id,
    'name' => [
        'fr' => 'Licence Professionnalisante Infirmier de Santé Publique',
        'ar' => 'الليسانس المهنية في التمريض للصحة العامة',
    ],
    'slug' => 'licence-infirmier-sante-publique',
    'description' => [
        'fr' => 'Formation de 3 ans menant à la Licence Professionnalisante Infirmier de Santé Publique.',
        'ar' => 'تدريب لمدة 3 سنوات يؤدي إلى الليسانس المهنية في التمريض للصحة العامة.',
    ],
    'duration' => '3 ans',  // ⚠️ IMPORTANT: Use "3 ans" or "5 ans"
    'order' => 1,
    'is_active' => true,
]);

echo "✅ Speciality created: {$speciality->name['fr']}\n";

// ============================================
// STEP 3: Create Years (1, 2, 3, 4, or 5)
// ============================================

$duration = 3; // Get from speciality: 3 or 5 years

for ($yearNumber = 1; $yearNumber <= $duration; $yearNumber++) {
    $year = Year::create([
        'speciality_id' => $speciality->id,
        'year_number' => $yearNumber,
        'name' => [
            'fr' => match($yearNumber) {
                1 => 'Première Année',
                2 => 'Deuxième Année',
                3 => 'Troisième Année',
                4 => 'Quatrième Année',
                5 => 'Cinquième Année',
                default => "Année {$yearNumber}",
            },
            'ar' => match($yearNumber) {
                1 => 'السنة الأولى',
                2 => 'السنة الثانية',
                3 => 'السنة الثالثة',
                4 => 'السنة الرابعة',
                5 => 'السنة الخامسة',
                default => "السنة {$yearNumber}",
            },
        ],
        'description' => [
            'fr' => "Année {$yearNumber} de {$speciality->name['fr']}",
            'ar' => "السنة {$yearNumber} من {$speciality->name['ar']}",
        ],
        'order' => $yearNumber,
        'is_active' => true,
    ]);

    echo "✅ Year {$yearNumber} created\n";

    // ============================================
    // STEP 4: Create Semesters (S1 and S2)
    // ============================================

    $academicYear = '2024-2025'; // Update this each academic year

    // Semester 1 (S1) - September to January
    $semester1 = Semester::create([
        'year_id' => $year->id,
        'semester_number' => 1,
        'name' => [
            'fr' => 'Semestre 1',
            'ar' => 'الفصل الأول',
        ],
        'start_date' => Carbon::create(2024, 9, 1),  // September 1
        'end_date' => Carbon::create(2025, 1, 31),  // January 31
        'academic_year' => $academicYear,
        'is_active' => true,
    ]);

    echo "  ✅ Semester 1 created for Year {$yearNumber}\n";

    // Semester 2 (S2) - February to June
    $semester2 = Semester::create([
        'year_id' => $year->id,
        'semester_number' => 2,
        'name' => [
            'fr' => 'Semestre 2',
            'ar' => 'الفصل الثاني',
        ],
        'start_date' => Carbon::create(2025, 2, 1),  // February 1
        'end_date' => Carbon::create(2025, 6, 30),  // June 30
        'academic_year' => $academicYear,
        'is_active' => true,
    ]);

    echo "  ✅ Semester 2 created for Year {$yearNumber}\n";
}

echo "\n🎉 Complete! All years and semesters created.\n";

// ============================================
// VERIFICATION
// ============================================

echo "\n📊 Verification:\n";
echo "Filière: {$filiere->name['fr']}\n";
echo "Speciality: {$speciality->name['fr']} ({$speciality->duration})\n";
echo "Years created: {$speciality->years()->count()}\n";
foreach ($speciality->years as $y) {
    echo "  - Year {$y->year_number}: {$y->name['fr']} ({$y->semesters()->count()} semesters)\n";
}

