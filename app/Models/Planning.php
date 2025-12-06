<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Planning extends Model
{
    protected $fillable = [
        'semester_id',
        'academic_year',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    /**
     * Get the semester that owns this planning.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    /**
     * Get the planning items for this planning.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PlanningItem::class, 'planning_id');
    }

    /**
     * Get planning items ordered by day and time.
     */
    public function orderedItems(): HasMany
    {
        return $this->items()->orderBy('day_of_week')->orderBy('start_time');
    }

    /**
     * Get planning items for a specific day.
     */
    public function itemsForDay(int $dayOfWeek): HasMany
    {
        return $this->items()->where('day_of_week', $dayOfWeek)->orderBy('start_time');
    }

    /**
     * Get planning items for a specific group.
     */
    public function itemsForGroup(int $groupId): HasMany
    {
        return $this->items()->where('group_id', $groupId)->orderBy('day_of_week')->orderBy('start_time');
    }

    /**
     * Get the schedule image for this planning's semester (if exists).
     */
    public function scheduleImage()
    {
        return $this->hasOne(\App\Models\ScheduleImage::class, 'semester_id', 'semester_id')
            ->where('is_active', true);
    }
}

