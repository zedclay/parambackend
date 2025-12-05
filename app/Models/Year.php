<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Year extends Model
{
    protected $fillable = [
        'speciality_id',
        'year_number',
        'name',
        'description',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'year_number' => 'integer',
            'order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the speciality that owns this year.
     */
    public function speciality(): BelongsTo
    {
        return $this->belongsTo(Speciality::class, 'speciality_id');
    }

    /**
     * Get the semesters for this year.
     */
    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class, 'year_id');
    }

    /**
     * Get the groups for this year.
     */
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class, 'year_id');
    }

    /**
     * Get the students in this year.
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'year_id')->where('role', 'student');
    }

    /**
     * Get the modules assigned to this year.
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'module_year_assignments', 'year_id', 'module_id')
            ->withPivot('semester_number', 'is_mandatory')
            ->withTimestamps();
    }

    /**
     * Get modules for a specific semester.
     */
    public function modulesForSemester(int $semesterNumber)
    {
        return $this->modules()->wherePivot('semester_number', $semesterNumber);
    }
}

