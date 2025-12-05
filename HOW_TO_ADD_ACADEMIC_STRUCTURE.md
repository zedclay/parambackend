# 📚 How to Add Academic Structure (Filière, Speciality, Years, Semesters)

## 🎯 Overview

This guide explains how to add:
- **Filières** (Study Programs)
- **Specialities** (Specializations within Filières)
- **Years** (1, 2, 3, 4, 5) - Based on speciality duration
- **Semesters** (S1 and S2) - Two per year

---

## 📋 Structure Hierarchy

```
Filière (e.g., "Soins Infirmiers")
  └── Speciality (e.g., "Licence Professionnalisante Infirmier de Santé Publique")
      └── Year 1
          ├── Semester 1 (S1)
          └── Semester 2 (S2)
      └── Year 2
          ├── Semester 1 (S1)
          └── Semester 2 (S2)
      └── Year 3
          ├── Semester 1 (S1)
          └── Semester 2 (S2)
      ... (up to Year 5 if 5-year program)
```

---

## 🚀 Method 1: Using Seeders (Recommended)

### Step 1: Run All Seeders

```bash
cd backend
php artisan db:seed
```

This will automatically create:
- All filières
- All specialities
- Years (1-5) based on speciality duration
- Semesters (S1, S2) for each year

### Step 2: Run Individual Seeders

```bash
# Seed filières only
php artisan db:seed --class=FiliereSeeder

# Seed specialities only
php artisan db:seed --class=SpecialitySeeder

# Seed years for all specialities
php artisan db:seed --class=YearSeeder

# Seed semesters for all years
php artisan db:seed --class=SemesterSeeder
```

---

## 🛠️ Method 2: Using Laravel Tinker (Interactive)

### Add a Filière

```bash
php artisan tinker
```

```php
use App\Models\Filiere;

$filiere = Filiere::create([
    'name' => [
        'fr' => 'Soins Infirmiers',
        'ar' => 'التمريض',
    ],
    'slug' => 'soins-infirmiers',
    'description' => [
        'fr' => 'Formation en soins infirmiers...',
        'ar' => 'تدريب في التمريض...',
    ],
    'order' => 1,
    'is_active' => true,
]);
```

### Add a Speciality

```php
use App\Models\Speciality;
use App\Models\Filiere;

$filiere = Filiere::where('slug', 'soins-infirmiers')->first();

$speciality = Speciality::create([
    'filiere_id' => $filiere->id,
    'name' => [
        'fr' => 'Licence Professionnalisante Infirmier de Santé Publique',
        'ar' => 'الليسانس المهنية في التمريض للصحة العامة',
    ],
    'slug' => 'licence-infirmier-sante-publique',
    'description' => [
        'fr' => 'Formation de 3 ans...',
        'ar' => 'تدريب لمدة 3 سنوات...',
    ],
    'duration' => '3 ans',  // or '5 ans' for 5-year programs
    'order' => 1,
    'is_active' => true,
]);
```

### Add Years (1-5)

```php
use App\Models\Year;
use App\Models\Speciality;

$speciality = Speciality::where('slug', 'licence-infirmier-sante-publique')->first();

// For 3-year program: Create Years 1, 2, 3
// For 5-year program: Create Years 1, 2, 3, 4, 5

$duration = 3; // or 5 for 5-year programs

for ($yearNumber = 1; $yearNumber <= $duration; $yearNumber++) {
    Year::create([
        'speciality_id' => $speciality->id,
        'year_number' => $yearNumber,
        'name' => [
            'fr' => match($yearNumber) {
                1 => 'Première Année',
                2 => 'Deuxième Année',
                3 => 'Troisième Année',
                4 => 'Quatrième Année',
                5 => 'Cinquième Année',
            },
            'ar' => match($yearNumber) {
                1 => 'السنة الأولى',
                2 => 'السنة الثانية',
                3 => 'السنة الثالثة',
                4 => 'السنة الرابعة',
                5 => 'السنة الخامسة',
            },
        ],
        'description' => [
            'fr' => "Année {$yearNumber}",
            'ar' => "السنة {$yearNumber}",
        ],
        'order' => $yearNumber,
        'is_active' => true,
    ]);
}
```

### Add Semesters (S1 and S2) for Each Year

```php
use App\Models\Semester;
use App\Models\Year;
use Carbon\Carbon;

$year = Year::where('year_number', 1)
    ->whereHas('speciality', function($q) {
        $q->where('slug', 'licence-infirmier-sante-publique');
    })
    ->first();

$academicYear = '2024-2025';

// Semester 1 (S1)
Semester::create([
    'year_id' => $year->id,
    'semester_number' => 1,
    'name' => [
        'fr' => 'Semestre 1',
        'ar' => 'الفصل الأول',
    ],
    'start_date' => Carbon::create(2024, 9, 1),  // September 1
    'end_date' => Carbon::create(2025, 1, 31),   // January 31
    'academic_year' => $academicYear,
    'is_active' => true,
]);

// Semester 2 (S2)
Semester::create([
    'year_id' => $year->id,
    'semester_number' => 2,
    'name' => [
        'fr' => 'Semestre 2',
        'ar' => 'الفصل الثاني',
    ],
    'start_date' => Carbon::create(2025, 2, 1),  // February 1
    'end_date' => Carbon::create(2025, 6, 30),   // June 30
    'academic_year' => $academicYear,
    'is_active' => true,
]);
```

---

## 🎨 Method 3: Using Admin Interface (Frontend)

Once the admin interface is set up, you can:

1. **Go to Admin Dashboard** → **Structure Académique**
2. **Select a Speciality** from the dropdown
3. **Click "Années" tab** → Click "Ajouter" to add years
4. **Click "Semestres" tab** → Click "Ajouter" to add semesters

---

## 📝 Complete Example: Adding a New Program

### Example: Adding "Médecine" (5-year program)

```php
use App\Models\Filiere;
use App\Models\Speciality;
use App\Models\Year;
use App\Models\Semester;
use Carbon\Carbon;

// 1. Create Filière
$filiere = Filiere::create([
    'name' => [
        'fr' => 'Médecine',
        'ar' => 'الطب',
    ],
    'slug' => 'medecine',
    'description' => [
        'fr' => 'Formation médicale',
        'ar' => 'التدريب الطبي',
    ],
    'order' => 10,
    'is_active' => true,
]);

// 2. Create Speciality (5-year program)
$speciality = Speciality::create([
    'filiere_id' => $filiere->id,
    'name' => [
        'fr' => 'Doctorat en Médecine',
        'ar' => 'دكتوراه في الطب',
    ],
    'slug' => 'doctorat-medecine',
    'description' => [
        'fr' => 'Formation de 5 ans en médecine',
        'ar' => 'تدريب لمدة 5 سنوات في الطب',
    ],
    'duration' => '5 ans',
    'order' => 1,
    'is_active' => true,
]);

// 3. Create Years 1-5
for ($yearNumber = 1; $yearNumber <= 5; $yearNumber++) {
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
            },
            'ar' => match($yearNumber) {
                1 => 'السنة الأولى',
                2 => 'السنة الثانية',
                3 => 'السنة الثالثة',
                4 => 'السنة الرابعة',
                5 => 'السنة الخامسة',
            },
        ],
        'order' => $yearNumber,
        'is_active' => true,
    ]);

    // 4. Create Semesters S1 and S2 for each year
    $academicYear = '2024-2025';
    
    // Semester 1
    Semester::create([
        'year_id' => $year->id,
        'semester_number' => 1,
        'name' => [
            'fr' => 'Semestre 1',
            'ar' => 'الفصل الأول',
        ],
        'start_date' => Carbon::create(2024, 9, 1),
        'end_date' => Carbon::create(2025, 1, 31),
        'academic_year' => $academicYear,
        'is_active' => true,
    ]);

    // Semester 2
    Semester::create([
        'year_id' => $year->id,
        'semester_number' => 2,
        'name' => [
            'fr' => 'Semestre 2',
            'ar' => 'الفصل الثاني',
        ],
        'start_date' => Carbon::create(2025, 2, 1),
        'end_date' => Carbon::create(2025, 6, 30),
        'academic_year' => $academicYear,
        'is_active' => true,
    ]);
}
```

---

## 🔍 Verification

### Check What Was Created

```php
// Check filières
$filieres = \App\Models\Filiere::all();
foreach ($filieres as $f) {
    echo "Filière: {$f->name['fr']}\n";
    foreach ($f->specialities as $spec) {
        echo "  - Speciality: {$spec->name['fr']} ({$spec->duration})\n";
        foreach ($spec->years as $year) {
            echo "    - Year {$year->year_number}: {$year->name['fr']}\n";
            foreach ($year->semesters as $semester) {
                echo "      - {$semester->name['fr']} ({$semester->academic_year})\n";
            }
        }
    }
}
```

---

## 📊 Data Structure Summary

### Filière Fields
- `name` (JSON: fr, ar)
- `slug` (unique)
- `description` (JSON: fr, ar)
- `order`
- `is_active`

### Speciality Fields
- `filiere_id` (foreign key)
- `name` (JSON: fr, ar)
- `slug` (unique)
- `description` (JSON: fr, ar)
- `duration` (e.g., "3 ans" or "5 ans")
- `order`
- `is_active`

### Year Fields
- `speciality_id` (foreign key)
- `year_number` (1, 2, 3, 4, or 5)
- `name` (JSON: fr, ar)
- `description` (JSON: fr, ar)
- `order`
- `is_active`

### Semester Fields
- `year_id` (foreign key)
- `semester_number` (1 or 2)
- `name` (JSON: fr, ar)
- `start_date` (date)
- `end_date` (date)
- `academic_year` (string, e.g., "2024-2025")
- `is_active`

---

## ⚠️ Important Notes

1. **Duration**: 
   - Set `duration` in speciality as "3 ans" or "5 ans"
   - YearSeeder automatically creates the correct number of years

2. **Semesters**: 
   - Always exactly 2 semesters per year (S1 and S2)
   - SemesterSeeder creates both automatically

3. **Academic Year**: 
   - Update `academic_year` when creating new semesters
   - Format: "2024-2025"

4. **Dates**: 
   - S1: Usually September to January
   - S2: Usually February to June
   - Adjust dates based on your academic calendar

5. **Order**: 
   - Years: 1, 2, 3, 4, 5
   - Semesters: 1, 2

---

## 🚀 Quick Start

To quickly set up the entire academic structure:

```bash
cd backend
php artisan migrate:fresh --seed
```

This will:
1. Drop all tables
2. Recreate all tables
3. Seed all data including:
   - Filières
   - Specialities
   - Years (1-5 based on duration)
   - Semesters (S1, S2 for each year)

---

**Last Updated:** 2025-12-05

