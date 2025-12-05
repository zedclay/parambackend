# 🔐 Admin CRUD Operations Guide

## ✅ Complete Admin Permissions

As an admin, you now have **full CRUD permissions** (Create, Read, Update, Delete) for all academic entities.

---

## 📋 Available Admin Operations

### 1. **Filières Management**
```
GET    /api/admin/filieres          - List all filières
POST   /api/admin/filieres          - Create new filière
GET    /api/admin/filieres/{id}     - Get filière details
PUT    /api/admin/filieres/{id}     - Update filière
DELETE /api/admin/filieres/{id}     - Delete filière
```

### 2. **Specialities Management**
```
GET    /api/admin/specialites          - List all specialities
POST   /api/admin/specialites          - Create new speciality
GET    /api/admin/specialites/{id}     - Get speciality details
PUT    /api/admin/specialites/{id}     - Update speciality
DELETE /api/admin/specialites/{id}     - Delete speciality
```

### 3. **Years Management** ✨ NEW
```
GET    /api/admin/years?speciality_id={id}  - List years (filter by speciality)
POST   /api/admin/years                      - Create new year
GET    /api/admin/years/{id}                 - Get year details
PUT    /api/admin/years/{id}                 - Update year
DELETE /api/admin/years/{id}                 - Delete year
```

**Create Year Example:**
```json
POST /api/admin/years
{
  "speciality_id": 1,
  "year_number": 1,
  "name": {
    "fr": "Première Année",
    "ar": "السنة الأولى"
  },
  "description": {
    "fr": "Description...",
    "ar": "وصف..."
  },
  "order": 1
}
```

### 4. **Semesters Management** ✨ NEW
```
GET    /api/admin/semesters?year_id={id}     - List semesters (filter by year)
GET    /api/admin/semesters?speciality_id={id} - List semesters (filter by speciality)
POST   /api/admin/semesters                   - Create new semester
GET    /api/admin/semesters/{id}              - Get semester details
PUT    /api/admin/semesters/{id}              - Update semester
DELETE /api/admin/semesters/{id}              - Delete semester
```

**Create Semester Example:**
```json
POST /api/admin/semesters
{
  "year_id": 1,
  "semester_number": 1,
  "name": {
    "fr": "Semestre 1",
    "ar": "الفصل الأول"
  },
  "start_date": "2024-09-01",
  "end_date": "2025-01-31",
  "academic_year": "2024-2025"
}
```

### 5. **Groups Management** ✨ NEW
```
GET    /api/admin/groups?speciality_id={id}  - List groups (filter by speciality)
GET    /api/admin/groups?year_id={id}         - List groups (filter by year)
POST   /api/admin/groups                      - Create new group
GET    /api/admin/groups/{id}                 - Get group details
PUT    /api/admin/groups/{id}                - Update group
DELETE /api/admin/groups/{id}                - Delete group
```

**Create Group Example:**
```json
POST /api/admin/groups
{
  "speciality_id": 1,
  "year_id": 1,
  "name": "G1",
  "capacity": 30
}
```

### 6. **Plannings Management** ✨ NEW
```
GET    /api/admin/plannings?semester_id={id}  - List plannings (filter by semester)
POST   /api/admin/plannings                   - Create new planning
GET    /api/admin/plannings/{id}              - Get planning details
PUT    /api/admin/plannings/{id}              - Update planning
DELETE /api/admin/plannings/{id}             - Delete planning
POST   /api/admin/plannings/{id}/publish      - Publish planning
POST   /api/admin/plannings/{id}/unpublish    - Unpublish planning
```

**Create Planning Example:**
```json
POST /api/admin/plannings
{
  "semester_id": 1,
  "academic_year": "2024-2025"
}
```

### 7. **Planning Items Management** ✨ NEW
```
GET    /api/admin/plannings/{planningId}/items  - List planning items
POST   /api/admin/plannings/{planningId}/items  - Create new planning item
GET    /api/admin/planning-items/{id}           - Get planning item details
PUT    /api/admin/planning-items/{id}            - Update planning item
DELETE /api/admin/planning-items/{id}           - Delete planning item
```

**Create Planning Item Example:**
```json
POST /api/admin/plannings/1/items
{
  "module_id": 5,
  "group_id": 1,
  "day_of_week": 1,
  "start_time": "08:00:00",
  "end_time": "10:00:00",
  "room": "Salle A101",
  "teacher_name": "Dr. Ahmed",
  "teacher_email": "ahmed@example.com",
  "course_type": "cours"
}
```

### 8. **Modules Management**
```
GET    /api/admin/modules          - List all modules
POST   /api/admin/modules          - Create new module
PUT    /api/admin/modules/{id}     - Update module
DELETE /api/admin/modules/{id}     - Delete module
```

### 9. **Students Management**
```
GET    /api/admin/students                    - List all students
POST   /api/admin/students                    - Create new student
GET    /api/admin/students/{id}               - Get student details
PUT    /api/admin/students/{id}               - Update student
DELETE /api/admin/students/{id}              - Delete student
POST   /api/admin/students/{id}/reset-password - Reset password
POST   /api/admin/students/{id}/assign-modules - Assign modules
```

### 10. **Notes Management**
```
GET    /api/admin/notes              - List all notes
POST   /api/admin/notes              - Create new note
POST   /api/admin/notes/bulk-upload  - Bulk upload notes
GET    /api/admin/notes/{id}         - Get note details
PUT    /api/admin/notes/{id}         - Update note
DELETE /api/admin/notes/{id}         - Delete note
POST   /api/admin/notes/{id}/assign  - Assign note to students
```

### 11. **Announcements Management**
```
GET    /api/admin/announcements          - List all announcements
POST   /api/admin/announcements          - Create new announcement
PUT    /api/admin/announcements/{id}     - Update announcement
DELETE /api/admin/announcements/{id}     - Delete announcement
```

---

## 🎯 How to Use (Frontend)

### Via Admin Dashboard

1. **Login as Admin**
   - Go to `/login`
   - Login with admin credentials

2. **Access Admin Dashboard**
   - Navigate to `/admin/dashboard`

3. **Manage Academic Structure**
   - Click **"Structure Académique"** tab
   - Select a speciality
   - Choose tab: **Years**, **Semesters**, or **Groups**
   - Click **"Ajouter"** to create
   - Click **Edit icon** to update
   - Click **Delete icon** to delete

4. **Manage Plannings**
   - Click **"Emplois du Temps"** tab
   - Select a semester
   - Click **"Créer l'emploi du temps"** if no planning exists
   - Click **"Ajouter un cours"** to add planning items
   - Click **Edit/Delete** icons on items to manage them
   - Click **"Publier"** to publish the planning

---

## 🔧 API Usage Examples

### Create a Year

```bash
curl -X POST https://infspsb.com/api/admin/years \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "speciality_id": 1,
    "year_number": 1,
    "name": {
      "fr": "Première Année",
      "ar": "السنة الأولى"
    },
    "order": 1
  }'
```

### Create a Semester

```bash
curl -X POST https://infspsb.com/api/admin/semesters \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "year_id": 1,
    "semester_number": 1,
    "name": {
      "fr": "Semestre 1",
      "ar": "الفصل الأول"
    },
    "start_date": "2024-09-01",
    "end_date": "2025-01-31",
    "academic_year": "2024-2025"
  }'
```

### Create a Group

```bash
curl -X POST https://infspsb.com/api/admin/groups \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "speciality_id": 1,
    "year_id": 1,
    "name": "G1",
    "capacity": 30
  }'
```

### Create a Planning Item

```bash
curl -X POST https://infspsb.com/api/admin/plannings/1/items \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "module_id": 5,
    "group_id": 1,
    "day_of_week": 1,
    "start_time": "08:00:00",
    "end_time": "10:00:00",
    "room": "Salle A101",
    "teacher_name": "Dr. Ahmed",
    "course_type": "cours"
  }'
```

---

## ⚠️ Important Notes

### Delete Restrictions

1. **Years**: Cannot delete if students are assigned
2. **Semesters**: Cannot delete if planning exists
3. **Groups**: Cannot delete if students are assigned

### Validation Rules

1. **Years**: 
   - `year_number` must be 1-5
   - Unique per speciality

2. **Semesters**: 
   - `semester_number` must be 1 or 2
   - Unique per year
   - `end_date` must be after `start_date`

3. **Groups**: 
   - Unique name per year and speciality
   - Code auto-generated

4. **Planning Items**: 
   - `day_of_week` must be 1-7
   - `end_time` must be after `start_time`
   - `course_type` must be: cours, td, tp, or examen

---

## 🎨 Frontend Components

### AcademicManagement Component
- **Location**: `frontend/src/components/admin/AcademicManagement.jsx`
- **Features**:
  - Create/Edit/Delete Years
  - Create/Edit/Delete Semesters
  - Create/Edit/Delete Groups
  - Filter by Speciality

### PlanningManagement Component
- **Location**: `frontend/src/components/admin/PlanningManagement.jsx`
- **Features**:
  - Create/Edit/Delete Plannings
  - Create/Edit/Delete Planning Items
  - Publish/Unpublish Planning
  - Week view calendar

---

## 📝 Complete Workflow Example

### Adding a Complete Academic Structure

1. **Create Filière** (if not exists)
   ```
   POST /api/admin/filieres
   ```

2. **Create Speciality**
   ```
   POST /api/admin/specialites
   {
     "filiere_id": 1,
     "duration": "3 ans"
   }
   ```

3. **Create Years** (1, 2, 3)
   ```
   POST /api/admin/years (for each year)
   ```

4. **Create Semesters** (S1, S2 for each year)
   ```
   POST /api/admin/semesters (for each semester)
   ```

5. **Create Groups** (G1, G2 for each year)
   ```
   POST /api/admin/groups (for each group)
   ```

6. **Create Planning** for a semester
   ```
   POST /api/admin/plannings
   ```

7. **Add Planning Items**
   ```
   POST /api/admin/plannings/{id}/items (for each course)
   ```

8. **Publish Planning**
   ```
   POST /api/admin/plannings/{id}/publish
   ```

---

## ✅ All Permissions Granted

As an admin, you can:
- ✅ **Create** any academic entity
- ✅ **Read/View** all data
- ✅ **Update** any entity
- ✅ **Delete** entities (with safety checks)
- ✅ **Manage** complete academic structure
- ✅ **Control** planning and schedules
- ✅ **Assign** students to years/groups
- ✅ **Publish/Unpublish** plannings

---

**Last Updated:** 2025-12-05  
**Status:** ✅ Complete Admin CRUD Operations Implemented

