<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $fillable = [
        'specialite_id',
        'code',
        'title',
        'description',
        'credits',
        'hours',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'description' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function speciality(): BelongsTo
    {
        return $this->belongsTo(Speciality::class, 'specialite_id');
    }

    public function enrolledStudents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'student_module_enrollments', 'module_id', 'student_id')
            ->withTimestamps()
            ->withPivot('enrolled_at');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'module_id');
    }

    /**
     * Get the years this module is assigned to.
     */
    public function years(): BelongsToMany
    {
        return $this->belongsToMany(Year::class, 'module_year_assignments', 'module_id', 'year_id')
            ->withPivot('semester_number', 'is_mandatory')
            ->withTimestamps();
    }

    /**
     * Get the planning items for this module.
     */
    public function planningItems(): HasMany
    {
        return $this->hasMany(PlanningItem::class, 'module_id');
    }

    /**
     * Check if module is assigned to a specific year and semester.
     */
    public function isAssignedToYearAndSemester(int $yearId, int $semesterNumber): bool
    {
        return $this->years()
            ->where('year_id', $yearId)
            ->wherePivot('semester_number', $semesterNumber)
            ->exists();
    }
}
