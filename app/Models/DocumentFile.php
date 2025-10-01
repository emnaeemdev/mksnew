<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DocumentFile extends Model
{
    protected $fillable = [
        'document_id',
        'file_path',
        'original_name',
        'display_name',
        'file_size',
        'mime_type',
        'sort_order'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'sort_order' => 'integer'
    ];

    /**
     * Get the document that owns this file.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the file URL.
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get the download URL.
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('documents.download', [
            'document' => $this->document->slug,
            'file' => $this->id
        ]);
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if file is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    /**
     * Check if file is a document.
     */
    public function isDocument(): bool
    {
        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        ];
        
        return in_array($this->mime_type, $documentTypes);
    }

    /**
     * Get file icon based on mime type.
     */
    public function getIconAttribute(): string
    {
        if ($this->isImage()) {
            return 'fas fa-image';
        }
        
        if ($this->mime_type === 'application/pdf') {
            return 'fas fa-file-pdf';
        }
        
        if (in_array($this->mime_type, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ])) {
            return 'fas fa-file-word';
        }
        
        if (in_array($this->mime_type, [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ])) {
            return 'fas fa-file-excel';
        }
        
        if (in_array($this->mime_type, [
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        ])) {
            return 'fas fa-file-powerpoint';
        }
        
        if ($this->mime_type === 'text/plain') {
            return 'fas fa-file-alt';
        }
        
        return 'fas fa-file';
    }
    
    /**
     * Get file icon based on mime type (method version).
     */
    public function getIcon(): string
    {
        return $this->getIconAttribute();
    }
    
    /**
     * Get formatted file size (method version).
     */
    public function getFormattedSize(): string
    {
        return $this->getFormattedSizeAttribute();
    }
}