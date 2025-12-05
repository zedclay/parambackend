<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Semester extends Model
{
    protected $fillable = [
        'year_id',
        'semester_number',
        'name',
        'start_date',
        'end_date',
        'academic_year',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'semester_number' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the year that owns this semester.
     */
    public function year(): BelongsTo
    {
        return $this->belongsTo(Year::class, 'year_id');
    }

    /**
     * Get the planning for this semester.
     */
    public function planning(): HasOne
    {
        return $this->hasOne(Planning::class, 'semester_id');
    }

    /**
     * Check if semester is currently active.
     */
    public function isCurrent(): bool
    {
        $today = now()->toDateString();
        return $this->start_date <= $today && $this->end_date >= $today;
    }
}

