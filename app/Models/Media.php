<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'alt_text',
        'description',
        'uploaded_by'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute()
    {
        $path = ltrim(str_replace('\\', '/', (string) $this->file_path), '/');

        // Full absolute URL including APP_URL (e.g. https://example.com/storage/media/file.pdf)
        return asset('storage/' . $path);
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getIsImageAttribute()
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getIsVideoAttribute()
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function getIsDocumentAttribute()
    {
        return in_array($this->mime_type, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain'
        ]);
    }

    public function getFileIconAttribute()
    {
        if ($this->is_image) {
            return 'fas fa-image';
        } elseif ($this->is_video) {
            return 'fas fa-video';
        } elseif ($this->is_document) {
            return 'fas fa-file-alt';
        } else {
            return 'fas fa-file';
        }
    }

    public function scopeOfType($query, $type)
    {
        return match($type) {
            'image' => $query->where('mime_type', 'like', 'image/%'),
            'video' => $query->where('mime_type', 'like', 'video/%'),
            'document' => $query->whereIn('mime_type', [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/plain'
            ]),
            default => $query
        };
    }
}