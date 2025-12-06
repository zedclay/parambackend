<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleImage extends Model
{
    protected $fillable = [
        'semester_id',
        'image_path',
        'original_filename',
        'uploaded_by',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the semester that owns this schedule image.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    /**
     * Get the admin user who uploaded this image.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the full URL to the schedule image.
     */
    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }

    /**
     * Check if image file exists on disk.
     */
    public function fileExists(): bool
    {
        return \Illuminate\Support\Facades\Storage::disk('public')->exists($this->image_path);
    }

    /**
     * Get file size in bytes.
     */
    public function getFileSizeAttribute(): ?int
    {
        if (!$this->fileExists()) {
            return null;
        }
        return \Illuminate\Support\Facades\Storage::disk('public')->size($this->image_path);
    }
}

