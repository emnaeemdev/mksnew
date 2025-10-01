<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Podcast extends Model
{
    protected $fillable = [
        'title', 'slug', 'content', 'cover_image_path', 'audio_path',
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
}
