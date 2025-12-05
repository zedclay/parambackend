# 🚀 Quick Reference Guide - New Architecture

## 📋 Running Migrations

```bash
cd backend
php artisan migrate
```

**Migration Order:**
1. Years table
2. Semesters table
3. Groups table
4. Plannings table
5. Planning Items table
6. Module-Year Assignments table
7. Update Users table (add student fields)

---

## 🎯 Common Queries

### Get Student with All Relationships

```php
$student = User::with(['year', 'filiere', 'speciality', 'group'])->find($id);
```

### Get All Students in a Group

```php
$group = Group::find(1);
$students = $group->students;
```

### Get All Students in a Year

```php
$year = Year::find(1);
$students = $year->students;
```

### Get Current Semester for a Student

```php
$student = User::find(1);
$currentSemester = $student->year
    ->semesters()
    ->where('start_date', '<=', now())
    ->where('end_date', '>=', now())
    ->first();
```

### Get Student's Schedule

```php
$student = User::with(['group', 'year.semesters.planning.items.module'])->find(1);
$currentSemester = $student->year->semesters()->where('is_active', true)->first();
$schedule = $currentSemester->planning->itemsForGroup($student->group_id)->get();
```

### Get Modules for Year and Semester

```php
$year = Year::find(1);
$s1Modules = $year->modulesForSemester(1)->get();
$s2Modules = $year->modulesForSemester(2)->get();
```

### Create a Complete Student

```php
$student = User::create([
    'name' => 'Hafid',
    'email' => 'hafid@example.com',
    'password' => Hash::make('password'),
    'role' => 'student',
    'year_id' => 1,
    'filiere_id' => 1,
    'speciality_id' => 1,
    'group_id' => 1,
    'student_number' => 'STU2024001',
]);
```

---

## 📊 Data Structure Example

### Student: Hafid
- **Filière:** Soins Infirmiers
- **Speciality:** Licence Professionnalisante Infirmier de Santé Publique
- **Year:** 1st Year
- **Group:** G1

### Database Representation

```php
// User (Student)
[
    'id' => 1,
    'name' => 'Hafid',
    'year_id' => 1,
    'filiere_id' => 1,
    'speciality_id' => 1,
    'group_id' => 1,
]

// Year
[
    'id' => 1,
    'speciality_id' => 1,
    'year_number' => 1,
    'name' => ['fr' => 'Première Année', 'ar' => 'السنة الأولى'],
]

// Group
[
    'id' => 1,
    'speciality_id' => 1,
    'year_id' => 1,
    'name' => 'G1',
    'code' => 'LPISP-1-G1',
]
```

---

## 🔍 Useful Model Methods

### Year Model
- `modulesForSemester($semesterNumber)` - Get modules for specific semester
- `students()` - Get all students in this year

### Semester Model
- `isCurrent()` - Check if semester is currently active
- `planning()` - Get the planning for this semester

### Group Model
- `hasAvailableSpots()` - Check if group has capacity
- `current_capacity` - Get current number of students

### Planning Model
- `itemsForDay($dayOfWeek)` - Get items for specific day
- `itemsForGroup($groupId)` - Get items for specific group
- `orderedItems()` - Get items ordered by day and time

### PlanningItem Model
- `dayName` - Get day name (Lundi, Mardi, etc.)
- `courseTypeLabel` - Get course type label (Cours, TD, TP, Examen)

### Speciality Model
- `getDurationInYears()` - Get duration as integer (3 or 5)

### Module Model
- `isAssignedToYearAndSemester($yearId, $semesterNumber)` - Check assignment

---

## ⚠️ Important Notes

1. **Student Fields are Nullable**: Admin users don't need year/filiere/speciality/group
2. **Unique Constraints**: 
   - Year number is unique per speciality
   - Semester number is unique per year
   - Group name is unique per year and speciality
3. **Cascade Deletes**: Most relationships cascade on delete
4. **Planning**: One planning per semester (unique constraint)

---

## 🐛 Troubleshooting

### Foreign Key Constraint Errors

If you get foreign key errors when running migrations:
1. Ensure `specialities` table exists (created earlier)
2. Run migrations in order
3. Check that all referenced tables exist

### Student Fields Not Showing

If student fields don't appear:
1. Run migration: `php artisan migrate`
2. Clear cache: `php artisan cache:clear`
3. Check that fields are in `$fillable` array

---

**Last Updated:** 2025-12-05

