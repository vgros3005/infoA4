<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'attachable_type', 'attachable_id', 'uploaded_by',
        'original_name', 'stored_name', 'disk', 'path',
        'mime_type', 'file_size', 'description',
        'is_pdf_version', 'pdf_version_number',
    ];

    protected function casts(): array
    {
        return [
            'is_pdf_version' => 'boolean',
            'file_size'      => 'integer',
        ];
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $iBytes = (int) $this->file_size;
        if ($iBytes < 1024) {
            return $iBytes . ' B';
        }
        if ($iBytes < 1048576) {
            return round($iBytes / 1024, 1) . ' KB';
        }
        return round($iBytes / 1048576, 1) . ' MB';
    }
}
