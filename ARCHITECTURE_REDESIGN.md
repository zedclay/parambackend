# 🏗️ Backend Architecture Redesign

## 📋 Academic Structure Requirements

### Core Entities

1. **Student** → belongs to:
   - One **Year** of study (1st, 2nd, 3rd, etc.)
   - One **Filière** (e.g., "Soins Infirmiers")
   - One **Speciality** (within the filière, e.g., "Licence Professionnalisante Infirmier de Santé Publique")
   - One **Group** (e.g., "G1")

2. **Filière** → contains:
   - One or multiple **Specialities**

3. **Speciality** → contains:
   - Multiple **Modules**
   - Has a **duration** (3 or 5 years)

4. **Year** → contains:
   - Two **Semesters** (S1, S2)

5. **Semester** → has:
   - One **Planning** (schedule)

6. **Planning** → contains:
   - Multiple **PlanningItems** (time slots with modules, groups, teachers, rooms)

---

## 🗄️ Database Schema

### Entity Relationship Diagram

```
┌─────────────┐
│   Filière   │
└──────┬──────┘
       │ has many
       ▼
┌─────────────┐
│ Speciality  │──────┐
└──────┬──────┘      │ has many
       │             ▼
       │         ┌─────────┐
       │         │ Module   │
       │         └────┬────┘
       │              │
       │              │ assigned to
       │              ▼
       │         ┌─────────┐
       │         │  Year    │
       │         └────┬────┘
       │              │ has many
       │              ▼
       │         ┌─────────┐
       │         │ Semester│
       │         └────┬────┘
       │              │ has one
       │              ▼
       │         ┌─────────┐
       │         │ Planning │
       │         └────┬────┘
       │              │ has many
       │              ▼
       │         ┌──────────────┐
       │         │PlanningItem  │
       │         └──────────────┘
       │
       │ belongs to
       ▼
┌─────────────┐
│    User     │──────┐
│  (Student)  │      │ belongs to
└──────┬──────┘      │
       │             ▼
       │         ┌─────────┐
       │         │  Group   │
       │         └─────────┘
       │
       │ belongs to
       ▼
┌─────────────┐
│    Year     │
└─────────────┘
```

---

## 📊 Table Structures

### 1. **years** table
```sql
- id (primary key)
- speciality_id (foreign key → specialities)
- year_number (integer: 1, 2, 3, 4, 5)
- name (json: multilingual)
- description (json: nullable, multilingual)
- order (integer)
- is_active (boolean)
- created_at, updated_at
```

### 2. **semesters** table
```sql
- id (primary key)
- year_id (foreign key → years)
- semester_number (integer: 1 or 2)
- name (json: multilingual, e.g., "S1", "S2")
- start_date (date)
- end_date (date)
- academic_year (string: e.g., "2024-2025")
- is_active (boolean)
- created_at, updated_at
```

### 3. **groups** table
```sql
- id (primary key)
- speciality_id (foreign key → specialities)
- year_id (foreign key → years)
- name (string: e.g., "G1", "G2")
- code (string: unique identifier)
- capacity (integer: max students)
- is_active (boolean)
- created_at, updated_at
```

### 4. **plannings** table
```sql
- id (primary key)
- semester_id (foreign key → semesters, unique)
- academic_year (string)
- is_published (boolean)
- created_at, updated_at
```

### 5. **planning_items** table
```sql
- id (primary key)
- planning_id (foreign key → plannings)
- module_id (foreign key → modules)
- group_id (foreign key → groups, nullable)
- day_of_week (integer: 1=Monday, 7=Sunday)
- start_time (time)
- end_time (time)
- room (string, nullable)
- teacher_name (string, nullable)
- teacher_email (string, nullable)
- course_type (enum: 'cours', 'td', 'tp', 'examen')
- order (integer)
- created_at, updated_at
```

### 6. **module_year_assignments** table (pivot)
```sql
- id (primary key)
- module_id (foreign key → modules)
- year_id (foreign key → years)
- semester_number (integer: 1 or 2)
- is_mandatory (boolean)
- created_at, updated_at
- unique(module_id, year_id, semester_number)
```

---

## 🔄 Updated Relationships

### User (Student) Model
```php
- belongsTo(Year::class)
- belongsTo(Filiere::class)
- belongsTo(Speciality::class)
- belongsTo(Group::class)
- belongsToMany(Module::class) // through enrollments
```

### Filiere Model
```php
- hasMany(Speciality::class)
- hasMany(User::class) // students
```

### Speciality Model
```php
- belongsTo(Filiere::class)
- hasMany(Module::class)
- hasMany(Year::class)
- hasMany(Group::class)
- hasMany(User::class) // students
```

### Year Model
```php
- belongsTo(Speciality::class)
- hasMany(Semester::class)
- hasMany(Group::class)
- hasMany(User::class) // students
- belongsToMany(Module::class) // through module_year_assignments
```

### Semester Model
```php
- belongsTo(Year::class)
- hasOne(Planning::class)
```

### Module Model
```php
- belongsTo(Speciality::class)
- belongsToMany(Year::class) // through module_year_assignments
- hasMany(PlanningItem::class)
- belongsToMany(User::class) // through enrollments
```

### Group Model
```php
- belongsTo(Speciality::class)
- belongsTo(Year::class)
- hasMany(User::class) // students
- hasMany(PlanningItem::class)
```

### Planning Model
```php
- belongsTo(Semester::class)
- hasMany(PlanningItem::class)
```

### PlanningItem Model
```php
- belongsTo(Planning::class)
- belongsTo(Module::class)
- belongsTo(Group::class, nullable)
```

---

## 📝 Example Data Structure

### Student: Hafid
```json
{
  "id": 1,
  "name": "Hafid",
  "email": "hafid@example.com",
  "year_id": 1,  // 1st year
  "filiere_id": 1,  // Soins Infirmiers
  "speciality_id": 1,  // Licence Professionnalisante Infirmier de Santé Publique
  "group_id": 1  // G1
}
```

### Year Structure
```json
{
  "id": 1,
  "speciality_id": 1,
  "year_number": 1,
  "name": {"fr": "Première Année", "ar": "السنة الأولى"}
}
```

### Semester Structure
```json
{
  "id": 1,
  "year_id": 1,
  "semester_number": 1,
  "name": {"fr": "Semestre 1", "ar": "الفصل الأول"},
  "start_date": "2024-09-01",
  "end_date": "2025-01-31",
  "academic_year": "2024-2025"
}
```

### Planning Item Example
```json
{
  "id": 1,
  "planning_id": 1,
  "module_id": 5,
  "group_id": 1,
  "day_of_week": 1,  // Monday
  "start_time": "08:00:00",
  "end_time": "10:00:00",
  "room": "Salle A101",
  "teacher_name": "Dr. Ahmed",
  "course_type": "cours"
}
```

---

## 🎯 Key Design Decisions

1. **Year belongs to Speciality**: Each speciality has its own set of years (1st, 2nd, 3rd, etc.)

2. **Module-Year Assignment**: Modules are assigned to specific years and semesters through a pivot table, allowing flexibility

3. **Group belongs to Year and Speciality**: Groups are specific to a year within a speciality (e.g., "G1" in 1st year of Speciality X)

4. **Planning is per Semester**: Each semester has one planning, containing multiple planning items

5. **PlanningItem can be group-specific**: Some courses might be for specific groups, others for all students in the year

6. **Duration in Speciality**: The `duration` field in specialities indicates total years (3 or 5), which determines how many years exist for that speciality

---

## 🔄 Migration Strategy

1. Create new tables: `years`, `semesters`, `groups`, `plannings`, `planning_items`, `module_year_assignments`
2. Add foreign keys to `users` table: `year_id`, `filiere_id`, `speciality_id`, `group_id`
3. Update `specialities` table: ensure `duration` field exists
4. Migrate existing data if needed
5. Update all models with new relationships

---

## ✅ Benefits of This Architecture

1. **Scalable**: Easy to add new years, semesters, groups
2. **Flexible**: Modules can be assigned to different years/semesters
3. **Organized**: Clear separation of concerns
4. **Queryable**: Easy to query students by year, group, speciality
5. **Planning Support**: Complete schedule management per semester
6. **Multilingual**: All text fields support multiple languages

---

**Last Updated:** 2025-12-05

