<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DownloadImage extends Model
{
    protected $fillable = [
        'download_id',
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

    public function download(): BelongsTo
    {
        return $this->belongsTo(Download::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }
        return Storage::disk('public')->url($this->image_path);
    }

    public function imageExists(): bool
    {
        if (!$this->image_path) {
            return false;
        }
        return Storage::disk('public')->exists($this->image_path);
    }
}
