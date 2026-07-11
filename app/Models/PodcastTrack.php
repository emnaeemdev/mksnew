<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PodcastTrack extends Model
{
    protected $fillable = [
        'podcast_id',
        'title',
        'description',
        'cover_image_path',
        'audio_path',
        'spotify_url',
        'apple_podcasts_url',
        'soundcloud_url',
        'youtube_url',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function podcast(): BelongsTo
    {
        return $this->belongsTo(Podcast::class);
    }

    public function displayTitle(?string $fallback = null): string
    {
        $title = trim((string) $this->title);
        if ($title !== '') {
            return $title;
        }

        return $fallback ?: ('حلقة ' . $this->id);
    }

    public function coverUrl(?string $podcastCover = null): string
    {
        if ($this->cover_image_path) {
            return asset('storage/' . $this->cover_image_path);
        }
        if ($podcastCover) {
            return asset('storage/' . $podcastCover);
        }

        return 'https://via.placeholder.com/120x120?text=Podcast';
    }

    /**
     * @return array<string, string>
     */
    public function socialLinks(): array
    {
        $links = [
            'spotify' => $this->spotify_url,
            'apple' => $this->apple_podcasts_url,
            'soundcloud' => $this->soundcloud_url,
            'youtube' => $this->youtube_url,
        ];

        return array_filter($links, fn ($url) => filled($url));
    }
}
