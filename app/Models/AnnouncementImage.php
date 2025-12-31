<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AnnouncementImage extends Model
{
    protected $fillable = [
        'announcement_id',
        'image_path',
        'image_filename',
        'mime_type',
        'file_size',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'order' => 'integer',
        ];
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    /**
     * Get the full URL to the image
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
