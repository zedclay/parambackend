<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanningItem extends Model
{
    protected $fillable = [
        'planning_id',
        'module_id',
        'group_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
        'teacher_name',
        'teacher_email',
        'course_type',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'order' => 'integer',
        ];
    }

    /**
     * Get the planning that owns this item.
     */
    public function planning(): BelongsTo
    {
        return $this->belongsTo(Planning::class, 'planning_id');
    }

    /**
     * Get the module for this planning item.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    /**
     * Get the group for this planning item (nullable).
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    /**
     * Get day name.
     */
    public function getDayNameAttribute(): string
    {
        $days = [
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            7 => 'Dimanche',
        ];
        return $days[$this->day_of_week] ?? 'Unknown';
    }

    /**
     * Get course type label.
     */
    public function getCourseTypeLabelAttribute(): string
    {
        $labels = [
            'cours' => 'Cours',
            'td' => 'TD',
            'tp' => 'TP',
            'examen' => 'Examen',
        ];
        return $labels[$this->course_type] ?? $this->course_type;
    }
}

