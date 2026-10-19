<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Concerns\HasKeywords;

class Nashra extends Model
{
    use HasKeywords;
    protected $fillable = [
        'title_ar',
        'title_en',
        'subtitle_ar',
        'subtitle_en',
        'google_drive_url',
        'google_drive_url_en',
        'google_sheet_id',
        'google_sheet_id_en',
        'published_at',
        'content_ar',
        'content_en',
        'featured_image',
        'pdf_file',
        'status',
        'sort_order',
        'download_count'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'status' => 'boolean',
        'download_count' => 'integer',
    ];

    /**
     * Parse a Google Sheets URL or bare spreadsheet ID.
     *
     * @return array{url: string, id: ?string}
     */
    public static function parseGoogleSheetReference(?string $raw): array
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return ['url' => '', 'id' => null];
        }

        $working = $raw;
        while (str_contains($working, 'docs.google.com/spreadsheets/d/')) {
            if (preg_match('#https?://docs\.google\.com/spreadsheets/d/(.+)$#i', $working, $matches)) {
                $inner = trim($matches[1]);
                if (str_starts_with($inner, 'http://') || str_starts_with($inner, 'https://')) {
                    $working = $inner;
                    continue;
                }

                $url = 'https://docs.google.com/spreadsheets/d/' . ltrim($inner, '/');
                $url = self::normalizeGoogleSheetUrl($url);
                $id = self::extractSheetIdFromUrl($url);

                return ['url' => $url, 'id' => $id];
            }

            break;
        }

        if (preg_match('/^[a-zA-Z0-9-_]{20,}$/', $working)) {
            return [
                'url' => 'https://docs.google.com/spreadsheets/d/' . $working . '/edit',
                'id' => $working,
            ];
        }

        if (preg_match_all('/\/spreadsheets\/d\/([a-zA-Z0-9-_]{20,})/', $working, $idMatches)) {
            $id = end($idMatches[1]);

            return [
                'url' => 'https://docs.google.com/spreadsheets/d/' . $id . '/edit',
                'id' => $id,
            ];
        }

        return ['url' => $raw, 'id' => null];
    }

    public static function normalizeGoogleSheetUrl(string $url): string
    {
        $url = rtrim(trim($url), '/');
        $url = preg_replace('#(/edit)+$#', '/edit', $url) ?? $url;
        $url = preg_replace('~(#gid=\d+)/edit$~', '$1', $url) ?? $url;

        return $url;
    }

    protected static function extractSheetIdFromUrl(string $url): ?string
    {
        if (preg_match_all('/\/spreadsheets\/d\/([a-zA-Z0-9-_]{20,})/', $url, $matches)) {
            return end($matches[1]) ?: null;
        }

        return null;
    }

    /**
     * استخراج معرف Google Sheet من الرابط
     */
    public function extractGoogleSheetId(): ?string
    {
        return self::parseGoogleSheetReference($this->google_drive_url)['id'];
    }

    /**
     * تحديث معرف Google Sheet تلقائياً عند حفظ الرابط
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($nashra) {
            if ($nashra->google_drive_url) {
                $parsed = self::parseGoogleSheetReference($nashra->google_drive_url);
                $nashra->google_drive_url = $parsed['url'];
                $nashra->google_sheet_id = $parsed['id'];
            }

            if ($nashra->google_drive_url_en) {
                $parsed = self::parseGoogleSheetReference($nashra->google_drive_url_en);
                $nashra->google_drive_url_en = $parsed['url'];
                $nashra->google_sheet_id_en = $parsed['id'];
            } else {
                $nashra->google_sheet_id_en = null;
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
     * حصر النشرات التي تحتوي على محتوى باللغة الحالية
     * (في الإنجليزية: النشرات التي لها عنوان إنجليزي فقط)
     */
    public function scopeForCurrentLocale($query)
    {
        if (app()->getLocale() === 'en') {
            $query->whereNotNull('title_en')->where('title_en', '!=', '');
        }

        return $query;
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

    /**
     * العنوان حسب اللغة الحالية (مع الرجوع للعربية عند عدم توفر الإنجليزية)
     */
    public function getTitleAttribute()
    {
        if (app()->getLocale() === 'ar') {
            return $this->title_ar;
        }

        return $this->title_en ?: $this->title_ar;
    }

    /**
     * العنوان الفرعي حسب اللغة الحالية
     */
    public function getSubtitleAttribute()
    {
        if (app()->getLocale() === 'ar') {
            return $this->subtitle_ar;
        }

        return $this->subtitle_en ?: $this->subtitle_ar;
    }

    /**
     * المحتوى حسب اللغة الحالية
     */
    public function getContentAttribute()
    {
        if (app()->getLocale() === 'ar') {
            return $this->content_ar;
        }

        return $this->content_en ?: $this->content_ar;
    }

    /**
     * رابط Google Sheets حسب اللغة الحالية (مع الرجوع للعربية عند عدم توفر الإنجليزية)
     */
    public function localizedGoogleDriveUrl(): ?string
    {
        if (app()->getLocale() === 'ar') {
            return $this->google_drive_url;
        }

        return $this->google_drive_url_en ?: $this->google_drive_url;
    }

    /**
     * معرف Google Sheet حسب اللغة الحالية
     */
    public function localizedGoogleSheetId(): ?string
    {
        if (app()->getLocale() === 'ar') {
            return $this->google_sheet_id;
        }

        return $this->google_sheet_id_en ?: $this->google_sheet_id;
    }
}
