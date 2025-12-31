<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Download extends Model
{
    protected $fillable = [
        'title',
        'content',
        'author_id',
        'is_published',
        'published_at',
        'target_audience',
        'file_filename',
        'file_path',
        'file_mime_type',
        'file_size',
        'image_filename',
        'image_path',
        'image_mime_type',
        'image_size',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'content' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'file_size' => 'integer',
            'image_size' => 'integer',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(DownloadImage::class)->orderBy('order');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }
        return Storage::disk('public')->url($this->image_path);
    }

    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }
        return Storage::disk('public')->url($this->file_path);
    }

    public function imageExists(): bool
    {
        if (!$this->image_path) {
            return false;
        }
        return Storage::disk('public')->exists($this->image_path);
    }
}
