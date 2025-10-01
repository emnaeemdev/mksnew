<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Nashra extends Model
{
    protected $fillable = [
        'title_ar',
        'subtitle_ar',
        'google_drive_url',
        'google_sheet_id',
        'published_at',
        'content_ar',
        'featured_image',
        'pdf_file',
        'status',
        'sort_order'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'status' => 'boolean',
    ];

    /**
     * استخراج معرف Google Sheet من الرابط
     */
    public function extractGoogleSheetId()
    {
        if (preg_match('/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $this->google_drive_url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * تحديث معرف Google Sheet تلقائياً عند حفظ الرابط
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($nashra) {
            if ($nashra->google_drive_url) {
                $nashra->google_sheet_id = $nashra->extractGoogleSheetId();
            }
        });
    }

    /**
     * Scope للنشرات المنشورة
     */
    public function scopePublished($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope للترتيب حسب تاريخ النشر
     */
    public function scopeOrderByPublished($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    /**
     * Scope للترتيب حسب sort_order
     */
    public function scopeOrderBySortOrder($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * الحصول على رابط الصورة المميزة
     */
    public function getFeaturedImageUrlAttribute()
    {
        if ($this->featured_image) {
            return Storage::url($this->featured_image);
        }
        return null;
    }

    /**
     * الحصول على رابط ملف PDF
     */
    public function getPdfFileUrlAttribute()
    {
        if ($this->pdf_file) {
            return Storage::url($this->pdf_file);
        }
        return null;
    }

    /**
     * تنسيق تاريخ النشر
     */
    public function getFormattedPublishedAtAttribute()
    {
        if ($this->published_at) {
            return $this->published_at->format('Y-m-d');
        }
        return null;
    }
}
