<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PostFile extends Model
{
    protected $fillable = [
        'post_id',
        'file_path',
        'original_name',
        'display_name',
        'file_size',
        'mime_type',
        'sort_order',
        'language',
        'download_count'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'sort_order' => 'integer',
        'download_count' => 'integer'
    ];

    
    public function scopeForLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'غير محدد';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    
    public function isDocument(): bool
    {
        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain'
        ];
        
        return in_array($this->mime_type, $documentTypes);
    }

    
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
