<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = [
        'speciality_id',
        'year_id',
        'name',
        'code',
        'capacity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the speciality that owns this group.
     */
    public function speciality(): BelongsTo
    {
        return $this->belongsTo(Speciality::class, 'speciality_id');
    }

    /**
     * Get the year that owns this group.
     */
    public function year(): BelongsTo
    {
        return $this->belongsTo(Year::class, 'year_id');
    }

    /**
     * Get the students in this group.
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'group_id')->where('role', 'student');
    }

    /**
     * Get the planning items for this group.
     */
    public function planningItems(): HasMany
    {
        return $this->hasMany(PlanningItem::class, 'group_id');
    }

    /**
     * Get the current number of students in the group.
     */
    public function getCurrentCapacityAttribute(): int
    {
        return $this->students()->count();
    }

    /**
     * Check if group has available spots.
     */
    public function hasAvailableSpots(): bool
    {
        if ($this->capacity === null) {
            return true;
        }
        return $this->current_capacity < $this->capacity;
    }
}

