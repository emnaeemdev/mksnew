<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use App\Models\PodcastTrack;
use App\Support\StreamsPodcastAudio;
use Illuminate\Http\Request;

class PodcastController extends Controller
{
    use StreamsPodcastAudio;

    public function index(Request $request)
    {
        $query = Podcast::query()->where('is_published', true)->withCount('tracks');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $podcasts = $query->orderBy('sort_order')
                          ->orderByDesc('published_at')
                          ->paginate(12)
                          ->withQueryString();

        $title = app()->isLocale('ar') ? 'بودكاست محكمة' : 'Court Podcast';
        $description = app()->isLocale('ar') ? 'استمع إلى حلقات بودكاست محكمة وآخر الحلقات المنشورة' : 'Listen to Court Podcast episodes and latest published shows';

        return view('frontend.podcasts.index', compact('podcasts', 'title', 'description'));
    }

    public function show($locale, Podcast $podcast)
    {
        if (!$podcast->is_published) {
            abort(404);
        }

        $podcast->load('tracks');
        $activeTrack = $podcast->primaryTrack();

        $title = $podcast->title;
        $description = strip_tags(mb_substr($podcast->content ?? '', 0, 160));

        return view('frontend.podcasts.show', compact('podcast', 'activeTrack', 'title', 'description'));
    }

    public function stream($locale, Podcast $podcast, Request $request)
    {
        if (!$podcast->is_published) {
            abort(404);
        }

        $track = $podcast->primaryTrack();
        if (!$track || !$track->audio_path) {
            abort(404);
        }

        return $this->streamAudioFile($track->audio_path, $request);
    }

    public function streamTrack($locale, Podcast $podcast, PodcastTrack $track, Request $request)
    {
        if (!$podcast->is_published || (int) $track->podcast_id !== (int) $podcast->id || !$track->audio_path) {
            abort(404);
        }

        return $this->streamAudioFile($track->audio_path, $request);
    }
}
