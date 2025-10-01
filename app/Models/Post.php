<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Post extends Model
{
    protected $fillable = [
        'category_id',
        'user_id',
        'title_ar',
        'title_en',
        'slug',
        'content_ar',
        'content_en',
        'background_image',
        'featured_image',
        'background_image_ar',
        'background_image_en',
        'featured_image_ar',
        'featured_image_en',
        'file_path',
        'file_name',
        'status',
        'show_in_slider',
        'show_in_releases',
        'show_in_other_reports',
        'show_in_slider_ar',
        'show_in_releases_ar',
        'show_in_other_reports_ar',
        'show_in_slider_en',
        'show_in_releases_en',
        'show_in_other_reports_en',
        'published_at',
        'views_count',
        'sort_order'
    ];

    protected $casts = [
        'show_in_slider' => 'boolean',
        'show_in_releases' => 'boolean',
        'show_in_other_reports' => 'boolean',
        'show_in_slider_ar' => 'boolean',
        'show_in_releases_ar' => 'boolean',
        'show_in_other_reports_ar' => 'boolean',
        'show_in_slider_en' => 'boolean',
        'show_in_releases_en' => 'boolean',
        'show_in_other_reports_en' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the user that owns the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the files for the post.
     */
    public function files(): HasMany
    {
        return $this->hasMany(PostFile::class)->orderBy('sort_order');
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($post) {
            if (empty($post->slug)) {
                // استخدم العنوان الإنجليزي إذا كان متوفراً، وإلا استخدم العربي
                $titleForSlug = $post->title_en ?: $post->title_ar;
                $post->slug = Str::slug($titleForSlug);
            }
        });
    }

    public function getTitleAttribute()
    {
        $locale = app()->getLocale();
        if ($locale === 'ar') {
            return $this->title_ar;
        } else {
            // إذا لم يكن هناك عنوان إنجليزي، استخدم العربي
            return $this->title_en ?: $this->title_ar;
        }
    }

    public function getContentAttribute()
    {
        $locale = app()->getLocale();
        if ($locale === 'ar') {
            return $this->content_ar;
        } else {
            // إذا لم يكن هناك محتوى إنجليزي، استخدم العربي
            return $this->content_en ?: $this->content_ar;
        }
    }
    
    public function hasEnglishVersion()
    {
        return !empty($this->title_en) && !empty($this->content_en);
    }
    
    public function getAvailableLanguages()
    {
        $languages = ['ar']; // العربية متوفرة دائماً
        if ($this->hasEnglishVersion()) {
            $languages[] = 'en';
        }
        return $languages;
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    public function scopeForSlider($query)
    {
        return $query->where('show_in_slider', true);
    }

    public function scopeForReleases($query)
    {
        return $query->where('show_in_releases', true);
    }

    public function scopeForOtherReports($query)
    {
        return $query->where('show_in_other_reports', true);
    }

    // Arabic language scopes
    public function scopeForSliderAr($query)
    {
        return $query->where('show_in_slider_ar', true);
    }

    public function scopeForReleasesAr($query)
    {
        return $query->where('show_in_releases_ar', true);
    }

    public function scopeForOtherReportsAr($query)
    {
        return $query->where('show_in_other_reports_ar', true);
    }

    // English language scopes
    public function scopeForSliderEn($query)
    {
        return $query->where('show_in_slider_en', true);
    }

    public function scopeForReleasesEn($query)
    {
        return $query->where('show_in_releases_en', true);
    }

    public function scopeForOtherReportsEn($query)
    {
        return $query->where('show_in_other_reports_en', true);
    }

    public function hasFile()
    {
        return !empty($this->file_path) && !empty($this->file_name);
    }

    public function getFileUrl()
    {
        return $this->hasFile() ? asset('storage/' . $this->file_path) : null;
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Get featured image based on current locale
     */
    public function getFeaturedImageAttribute()
    {
        $locale = app()->getLocale();
        
        if ($locale === 'ar' && $this->featured_image_ar) {
            return $this->featured_image_ar;
        } elseif ($locale === 'en' && $this->featured_image_en) {
            return $this->featured_image_en;
        }
        
        // Fallback to original featured_image or Arabic version
        return $this->attributes['featured_image'] ?? $this->featured_image_ar;
    }

    /**
     * Get background image based on current locale
     */
    public function getBackgroundImageAttribute()
    {
        $locale = app()->getLocale();
        
        if ($locale === 'ar' && $this->background_image_ar) {
            return $this->background_image_ar;
        } elseif ($locale === 'en' && $this->background_image_en) {
            return $this->background_image_en;
        }
        
        // Fallback to original background_image or Arabic version
        return $this->attributes['background_image'] ?? $this->background_image_ar;
    }

    /**
     * Get files for specific language
     */
    public function getFilesForLanguage($language = null)
    {
        $language = $language ?? app()->getLocale();
        return $this->files()->where('language', $language)->orderBy('sort_order')->get();
    }
}
