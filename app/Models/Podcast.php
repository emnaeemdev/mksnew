<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Concerns\HasKeywords;

class Podcast extends Model
{
    use HasKeywords;
    protected $fillable = [
        'title', 'slug', 'content', 'series_label', 'cover_image_path', 'audio_path',
        'spotify_url', 'apple_podcasts_url', 'soundcloud_url', 'youtube_url',
        'published_at', 'is_published', 'sort_order'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug) && !empty($model->title)) {
                $model->slug = Str::slug($model->title);
            }
        });
    }

    public function tracks(): HasMany
    {
        // الأحدث أولاً للزائر
        return $this->hasMany(PodcastTrack::class)->orderByDesc('sort_order')->orderByDesc('id');
    }

    public function primaryTrack(): ?PodcastTrack
    {
        if ($this->relationLoaded('tracks')) {
            return $this->tracks->first();
        }

        return $this->tracks()->first();
    }

    public function seriesHeading(): string
    {
        $label = trim((string) $this->series_label);

        return $label !== '' ? $label : 'حلقات هذه السلسلة';
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if (!$this->cover_image_path) {
            return null;
        }

        return Storage::url($this->cover_image_path);
    }
}
