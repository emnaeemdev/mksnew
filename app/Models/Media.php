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

    /**
     * Get the user who uploaded the media.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the full URL of the media file.
     */
    public function getUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get the formatted file size.
     */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if the media is an image.
     */
    public function getIsImageAttribute()
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if the media is a video.
     */
    public function getIsVideoAttribute()
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Check if the media is a document.
     */
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

    /**
     * Get the file type icon.
     */
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

    /**
     * Scope to filter by file type.
     */
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