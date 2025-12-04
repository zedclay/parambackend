<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Note extends Model
{
    protected $fillable = [
        'module_id',
        'specialite_id',
        'uploader_id',
        'assigned_student_id',
        'title',
        'description',
        'filename',
        'stored_filename',
        'file_path',
        'mime_type',
        'file_size',
        'visibility',
        'download_count',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'download_count' => 'integer',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function speciality(): BelongsTo
    {
        return $this->belongsTo(Speciality::class, 'specialite_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function assignedStudent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_student_id');
    }

    public function downloadLogs(): HasMany
    {
        return $this->hasMany(DownloadLog::class, 'note_id');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function isImage(): bool
    {
        return in_array($this->mime_type, ['image/jpeg', 'image/jpg', 'image/png']);
    }
}
