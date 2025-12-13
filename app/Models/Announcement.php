<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'author_id',
        'is_published',
        'published_at',
        'target_audience',
        'image_filename',
        'image_path',
        'pdf_filename',
        'pdf_path',
        'image_mime_type',
        'image_size',
        'pdf_mime_type',
        'pdf_size',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'content' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'image_size' => 'integer',
            'pdf_size' => 'integer',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the full URL to the announcement image
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    /**
     * Get the full URL to the announcement PDF
     */
    public function getPdfUrlAttribute(): ?string
    {
        if (!$this->pdf_path) {
            return null;
        }

        return Storage::disk('public')->url($this->pdf_path);
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

    /**
     * Check if PDF file exists
     */
    public function pdfExists(): bool
    {
        if (!$this->pdf_path) {
            return false;
        }

        return Storage::disk('public')->exists($this->pdf_path);
    }
}
