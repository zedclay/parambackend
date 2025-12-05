<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Filiere extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_url',
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

    public function specialities(): HasMany
    {
        return $this->hasMany(Speciality::class, 'filiere_id');
    }

    public function activeSpecialities(): HasMany
    {
        return $this->specialities()->where('is_active', true);
    }

    /**
     * Get the students in this filière.
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'filiere_id')->where('role', 'student');
    }
}
