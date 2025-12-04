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
}
