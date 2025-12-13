<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HeroSlide extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'image_path',
        'image_filename',
        'order',
        'is_active',
        'gradient',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'subtitle' => 'array',
            'order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the full URL to the hero slide image
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    /**
     * Check if image file exists
     */
    public function imageExists(): bool
    {
        if (!$this->image_path) {
            return false;
        }

        return Storage::disk('public')->exists($this->image_path);
    }
}
