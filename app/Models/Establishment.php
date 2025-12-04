<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Establishment extends Model
{
    protected $fillable = [
        'specialite_id',
        'name',
        'address',
        'contact_email',
        'contact_phone',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
        ];
    }

    public function speciality(): BelongsTo
    {
        return $this->belongsTo(Speciality::class, 'specialite_id');
    }
}
