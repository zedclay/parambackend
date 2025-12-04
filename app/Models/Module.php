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
}
