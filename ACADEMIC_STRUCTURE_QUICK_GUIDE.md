# 🚀 Quick Guide: Adding Academic Structure

## 📋 What You Need to Know

- **Filière**: Study program (e.g., "Soins Infirmiers")
- **Speciality**: Specialization within filière (e.g., "Licence Professionnalisante Infirmier de Santé Publique")
- **Years**: Academic years (1, 2, 3, 4, or 5) - depends on speciality duration
- **Semesters**: Always 2 per year (S1 and S2)

---

## ⚡ Fastest Way: Use Seeders

```bash
cd backend
php artisan db:seed
```

This automatically creates:
- ✅ All filières
- ✅ All specialities  
- ✅ Years 1-5 (based on duration: 3 or 5 years)
- ✅ Semesters S1 and S2 for each year

---

## 📝 Manual Addition: Step by Step

### 1. Add Filière

```php
use App\Models\Filiere;

Filiere::create([
    'name' => ['fr' => 'Soins Infirmiers', 'ar' => 'التمريض'],
    'slug' => 'soins-infirmiers',
    'description' => ['fr' => '...', 'ar' => '...'],
    'order' => 1,
    'is_active' => true,
]);
```

### 2. Add Speciality

```php
use App\Models\Speciality;

$filiere = Filiere::where('slug', 'soins-infirmiers')->first();

Speciality::create([
    'filiere_id' => $filiere->id,
    'name' => ['fr' => 'Licence Professionnalisante...', 'ar' => '...'],
    'slug' => 'licence-infirmier-sante-publique',
    'duration' => '3 ans',  // ⚠️ "3 ans" or "5 ans"
    'order' => 1,
    'is_active' => true,
]);
```

### 3. Add Years (1-5)

**For 3-year program:**
```php
use App\Models\Year;

$speciality = Speciality::where('slug', 'licence-infirmier-sante-publique')->first();

// Create Years 1, 2, 3
for ($i = 1; $i <= 3; $i++) {
    Year::create([
        'speciality_id' => $speciality->id,
        'year_number' => $i,
        'name' => [
            'fr' => match($i) {
                1 => 'Première Année',
                2 => 'Deuxième Année',
                3 => 'Troisième Année',
            },
            'ar' => match($i) {
                1 => 'السنة الأولى',
                2 => 'السنة الثانية',
                3 => 'السنة الثالثة',
            },
        ],
        'order' => $i,
        'is_active' => true,
    ]);
}
```

**For 5-year program:**
```php
// Same code, but change loop to: for ($i = 1; $i <= 5; $i++)
// And add cases for 4 and 5 in the match statement
```

### 4. Add Semesters (S1 and S2)

```php
use App\Models\Semester;
use Carbon\Carbon;

$year = Year::where('year_number', 1)
    ->whereHas('speciality', fn($q) => $q->where('slug', 'licence-infirmier-sante-publique'))
    ->first();

// Semester 1
Semester::create([
    'year_id' => $year->id,
    'semester_number' => 1,
    'name' => ['fr' => 'Semestre 1', 'ar' => 'الفصل الأول'],
    'start_date' => Carbon::create(2024, 9, 1),
    'end_date' => Carbon::create(2025, 1, 31),
    'academic_year' => '2024-2025',
    'is_active' => true,
]);

// Semester 2
Semester::create([
    'year_id' => $year->id,
    'semester_number' => 2,
    'name' => ['fr' => 'Semestre 2', 'ar' => 'الفصل الثاني'],
    'start_date' => Carbon::create(2025, 2, 1),
    'end_date' => Carbon::create(2025, 6, 30),
    'academic_year' => '2024-2025',
    'is_active' => true,
]);
```

---

## 🎯 Complete Example in One Go

```php
use App\Models\{Filiere, Speciality, Year, Semester};
use Carbon\Carbon;

// 1. Filière
$filiere = Filiere::create([
    'name' => ['fr' => 'Soins Infirmiers', 'ar' => 'التمريض'],
    'slug' => 'soins-infirmiers',
    'order' => 1,
    'is_active' => true,
]);

// 2. Speciality (3-year program)
$speciality = Speciality::create([
    'filiere_id' => $filiere->id,
    'name' => ['fr' => 'Licence Professionnalisante...', 'ar' => '...'],
    'slug' => 'licence-infirmier-sante-publique',
    'duration' => '3 ans',
    'order' => 1,
    'is_active' => true,
]);

// 3. Years 1-3
for ($y = 1; $y <= 3; $y++) {
    $year = Year::create([
        'speciality_id' => $speciality->id,
        'year_number' => $y,
        'name' => [
            'fr' => match($y) { 1 => 'Première', 2 => 'Deuxième', 3 => 'Troisième' } . ' Année',
            'ar' => "السنة " . match($y) { 1 => 'الأولى', 2 => 'الثانية', 3 => 'الثالثة' },
        ],
        'order' => $y,
        'is_active' => true,
    ]);

    // 4. Semesters S1 and S2
    foreach ([1, 2] as $semNum) {
        Semester::create([
            'year_id' => $year->id,
            'semester_number' => $semNum,
            'name' => ['fr' => "Semestre {$semNum}", 'ar' => $semNum == 1 ? 'الفصل الأول' : 'الفصل الثاني'],
            'start_date' => $semNum == 1 ? Carbon::create(2024, 9, 1) : Carbon::create(2025, 2, 1),
            'end_date' => $semNum == 1 ? Carbon::create(2025, 1, 31) : Carbon::create(2025, 6, 30),
            'academic_year' => '2024-2025',
            'is_active' => true,
        ]);
    }
}
```

---

## 🔍 Verify What Was Created

```php
// Check structure
$filiere = Filiere::with(['specialities.years.semesters'])->first();
echo "Filière: {$filiere->name['fr']}\n";
foreach ($filiere->specialities as $spec) {
    echo "  Speciality: {$spec->name['fr']} ({$spec->duration})\n";
    foreach ($spec->years as $year) {
        echo "    Year {$year->year_number}: {$year->name['fr']}\n";
        foreach ($year->semesters as $sem) {
            echo "      - {$sem->name['fr']} ({$sem->academic_year})\n";
        }
    }
}
```

---

## 📅 Important Dates

**Semester 1 (S1):**
- Start: September 1
- End: January 31

**Semester 2 (S2):**
- Start: February 1
- End: June 30

**Academic Year Format:** "2024-2025"

---

## ⚠️ Key Points

1. **Duration**: Set in speciality as `"3 ans"` or `"5 ans"`
2. **Years**: Always create 1, 2, 3 (for 3-year) or 1, 2, 3, 4, 5 (for 5-year)
3. **Semesters**: Always exactly 2 per year (S1 and S2)
4. **Order**: Years are numbered 1-5, Semesters are numbered 1-2

---

## 🚀 Run Seeders

```bash
# Run all seeders (recommended)
php artisan db:seed

# Or run individually
php artisan db:seed --class=FiliereSeeder
php artisan db:seed --class=SpecialitySeeder
php artisan db:seed --class=YearSeeder
php artisan db:seed --class=SemesterSeeder
php artisan db:seed --class=GroupSeeder
```

---

**See `HOW_TO_ADD_ACADEMIC_STRUCTURE.md` for detailed documentation.**

