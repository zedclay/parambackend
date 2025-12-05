<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Speciality extends Model
{
    protected $fillable = [
        'filiere_id',
        'name',
        'slug',
        'description',
        'image_url',
        'duration',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class, 'filiere_id');
    }

    public function establishments(): HasMany
    {
        return $this->hasMany(Establishment::class, 'specialite_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class, 'specialite_id');
    }

    public function activeModules(): HasMany
    {
        return $this->modules()->where('is_active', true);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'specialite_id');
    }

    /**
     * Get the years for this speciality.
     */
    public function years(): HasMany
    {
        return $this->hasMany(Year::class, 'speciality_id');
    }

    /**
     * Get the groups for this speciality.
     */
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class, 'speciality_id');
    }

    /**
     * Get the students in this speciality.
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'speciality_id')->where('role', 'student');
    }

    /**
     * Get the duration in years (3 or 5).
     */
    public function getDurationInYears(): ?int
    {
        if (!$this->duration) {
            return null;
        }
        
        // Extract number from duration string (e.g., "3 years" -> 3)
        preg_match('/(\d+)/', $this->duration, $matches);
        return isset($matches[1]) ? (int) $matches[1] : null;
    }
}
