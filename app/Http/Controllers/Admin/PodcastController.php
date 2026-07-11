<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use App\Models\PodcastTrack;
use App\Support\StreamsPodcastAudio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PodcastController extends Controller
{
    use StreamsPodcastAudio;

    public function index(Request $request)
    {
        $query = Podcast::query()->withCount('tracks');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($inner) use ($q) {
                $inner->where('title', 'LIKE', "%$q%")
                    ->orWhere('content', 'LIKE', "%$q%");
            });
        }
        if ($request->filled('published')) {
            $query->where('is_published', $request->published === '1');
        }

        $podcasts = $query->orderBy('sort_order')->orderByDesc('published_at')->paginate(15);
        return view('admin.podcasts.index', compact('podcasts'));
    }

    public function create()
    {
        return view('admin.podcasts.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatePodcast($request);
        $tracksInput = $this->validateTracksInput($request, requiredAudio: true);

        $podcast = new Podcast();
        $this->fillPodcast($podcast, $data);
        $podcast->slug = $this->uniqueSlug($data['slug'] ?? Str::slug($data['title']));

        if ($request->hasFile('cover_image')) {
            $podcast->cover_image_path = $request->file('cover_image')->store('podcasts/covers', 'public');
        }

        $podcast->audio_path = null;
        $podcast->save();

        $this->syncNewTracks($podcast, $tracksInput['tracks'] ?? [], $request);
        $podcast->syncKeywordNames($request->input('keywords'));

        return redirect()->route('admin.podcasts.edit', $podcast)->with('success', 'تم إنشاء البودكاست بنجاح');
    }

    public function show(Podcast $podcast)
    {
        $podcast->load('tracks');
        return view('admin.podcasts.show', compact('podcast'));
    }

    public function edit(Podcast $podcast)
    {
        $podcast->load(['tracks', 'keywords']);
        return view('admin.podcasts.edit', compact('podcast'));
    }

    public function update(Request $request, Podcast $podcast)
    {
        $data = $this->validatePodcast($request, $podcast->id);
        $tracksInput = $this->validateTracksInput($request, requiredAudio: false);

        $this->fillPodcast($podcast, $data);
        if (!empty($data['slug'])) {
            $podcast->slug = $this->uniqueSlug($data['slug'], $podcast->id);
        }

        if ($request->hasFile('cover_image')) {
            if ($podcast->cover_image_path) {
                Storage::disk('public')->delete($podcast->cover_image_path);
            }
            $podcast->cover_image_path = $request->file('cover_image')->store('podcasts/covers', 'public');
        }

        $podcast->save();

        // حذف حلقات محددة
        $deleteIds = array_filter(array_map('intval', (array) $request->input('delete_track_ids', [])));
        if ($deleteIds !== []) {
            $toDelete = $podcast->tracks()->whereIn('id', $deleteIds)->get();
            foreach ($toDelete as $track) {
                if ($track->audio_path) {
                    Storage::disk('public')->delete($track->audio_path);
                }
                if ($track->cover_image_path) {
                    Storage::disk('public')->delete($track->cover_image_path);
                }
                $track->delete();
            }
        }

        // تحديث الحلقات الحالية
        foreach ((array) $request->input('existing_tracks', []) as $trackId => $trackData) {
            $track = $podcast->tracks()->where('id', (int) $trackId)->first();
            if (!$track) {
                continue;
            }
            $track->title = trim((string) ($trackData['title'] ?? '')) ?: null;
            $track->description = trim((string) ($trackData['description'] ?? '')) ?: null;
            $track->spotify_url = trim((string) ($trackData['spotify_url'] ?? '')) ?: null;
            $track->apple_podcasts_url = trim((string) ($trackData['apple_podcasts_url'] ?? '')) ?: null;
            $track->soundcloud_url = trim((string) ($trackData['soundcloud_url'] ?? '')) ?: null;
            $track->youtube_url = trim((string) ($trackData['youtube_url'] ?? '')) ?: null;
            if (isset($trackData['sort_order']) && $trackData['sort_order'] !== '') {
                $track->sort_order = (int) $trackData['sort_order'];
            }
            $coverFile = $request->file('existing_tracks.' . $trackId . '.cover');
            if ($coverFile) {
                if ($track->cover_image_path) {
                    Storage::disk('public')->delete($track->cover_image_path);
                }
                $track->cover_image_path = $coverFile->store('podcasts/covers', 'public');
            }
            $audioFile = $request->file('existing_tracks.' . $trackId . '.audio');
            if ($audioFile) {
                if ($track->audio_path) {
                    Storage::disk('public')->delete($track->audio_path);
                }
                $track->audio_path = $audioFile->store('podcasts/audio', 'public');
            }
            $track->save();
        }

        // إضافة حلقات جديدة (تظهر أولاً)
        $this->syncNewTracks($podcast, $tracksInput['tracks'] ?? [], $request);

        // مزامنة audio_path القديم للتوافق
        $primary = $podcast->fresh()->primaryTrack();
        $podcast->audio_path = $primary?->audio_path;
        $podcast->save();
        $podcast->syncKeywordNames($request->input('keywords'));

        return redirect()->route('admin.podcasts.edit', $podcast)->with('success', 'تم تحديث البودكاست بنجاح');
    }

    public function destroy(Podcast $podcast)
    {
        $podcast->load('tracks');
        foreach ($podcast->tracks as $track) {
            if ($track->audio_path) {
                Storage::disk('public')->delete($track->audio_path);
            }
            if ($track->cover_image_path) {
                Storage::disk('public')->delete($track->cover_image_path);
            }
        }
        if ($podcast->cover_image_path) {
            Storage::disk('public')->delete($podcast->cover_image_path);
        }
        if ($podcast->audio_path) {
            Storage::disk('public')->delete($podcast->audio_path);
        }
        $podcast->delete();
        return redirect()->route('admin.podcasts.index')->with('success', 'تم حذف البودكاست بنجاح');
    }

    public function stream(Request $request, Podcast $podcast)
    {
        $track = $podcast->primaryTrack();
        if (!$track || !$track->audio_path) {
            abort(404);
        }

        return $this->streamAudioFile($track->audio_path, $request);
    }

    public function streamTrack(Request $request, Podcast $podcast, PodcastTrack $track)
    {
        if ((int) $track->podcast_id !== (int) $podcast->id || !$track->audio_path) {
            abort(404);
        }

        return $this->streamAudioFile($track->audio_path, $request);
    }

    protected function validatePodcast(Request $request, ?int $podcastId = null): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:podcasts,slug' . ($podcastId ? ',' . $podcastId : ''),
            'content' => 'nullable|string',
            'series_label' => 'nullable|string|max:255',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'spotify_url' => 'nullable|url|max:255',
            'apple_podcasts_url' => 'nullable|url|max:255',
            'soundcloud_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'published_at' => 'nullable|date',
            'is_published' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'delete_track_ids' => 'nullable|array',
            'delete_track_ids.*' => 'integer',
            'existing_tracks' => 'nullable|array',
            'existing_tracks.*.cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'existing_tracks.*.audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a,aac|max:51200',
            'existing_tracks.*.spotify_url' => 'nullable|url|max:255',
            'existing_tracks.*.apple_podcasts_url' => 'nullable|url|max:255',
            'existing_tracks.*.soundcloud_url' => 'nullable|url|max:255',
            'existing_tracks.*.youtube_url' => 'nullable|url|max:255',
        ]);
    }

    protected function validateTracksInput(Request $request, bool $requiredAudio): array
    {
        return $request->validate([
            'tracks' => ($requiredAudio ? 'required' : 'nullable') . '|array' . ($requiredAudio ? '|min:1' : ''),
            'tracks.*.title' => 'nullable|string|max:255',
            'tracks.*.description' => 'nullable|string',
            'tracks.*.spotify_url' => 'nullable|url|max:255',
            'tracks.*.apple_podcasts_url' => 'nullable|url|max:255',
            'tracks.*.soundcloud_url' => 'nullable|url|max:255',
            'tracks.*.youtube_url' => 'nullable|url|max:255',
            'tracks.*.cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'tracks.*.audio' => ($requiredAudio ? 'required' : 'nullable') . '|file|mimes:mp3,wav,ogg,m4a,aac|max:51200',
        ]);
    }

    protected function fillPodcast(Podcast $podcast, array $data): void
    {
        $podcast->title = $data['title'];
        $podcast->content = $data['content'] ?? null;
        $podcast->series_label = trim((string) ($data['series_label'] ?? '')) ?: null;
        $podcast->spotify_url = $data['spotify_url'] ?? null;
        $podcast->apple_podcasts_url = $data['apple_podcasts_url'] ?? null;
        $podcast->soundcloud_url = $data['soundcloud_url'] ?? null;
        $podcast->youtube_url = $data['youtube_url'] ?? null;
        $podcast->published_at = $data['published_at'] ?? null;
        $podcast->is_published = (bool) ($data['is_published'] ?? false);
        $podcast->sort_order = $data['sort_order'] ?? 0;
    }

    protected function syncNewTracks(Podcast $podcast, array $tracks, Request $request): void
    {
        $maxOrder = (int) $podcast->tracks()->max('sort_order');
        $files = $request->file('tracks', []);

        $entries = [];
        foreach ($tracks as $index => $trackData) {
            $file = $files[$index]['audio'] ?? null;
            if (!$file) {
                continue;
            }
            $entries[] = [
                'title' => trim((string) ($trackData['title'] ?? '')) ?: null,
                'description' => trim((string) ($trackData['description'] ?? '')) ?: null,
                'spotify_url' => trim((string) ($trackData['spotify_url'] ?? '')) ?: null,
                'apple_podcasts_url' => trim((string) ($trackData['apple_podcasts_url'] ?? '')) ?: null,
                'soundcloud_url' => trim((string) ($trackData['soundcloud_url'] ?? '')) ?: null,
                'youtube_url' => trim((string) ($trackData['youtube_url'] ?? '')) ?: null,
                'file' => $file,
                'cover' => $files[$index]['cover'] ?? null,
            ];
        }

        foreach (array_reverse($entries) as $entry) {
            $maxOrder++;
            $path = $entry['file']->store('podcasts/audio', 'public');
            $coverPath = null;
            if (!empty($entry['cover'])) {
                $coverPath = $entry['cover']->store('podcasts/covers', 'public');
            }
            $podcast->tracks()->create([
                'title' => $entry['title'],
                'description' => $entry['description'],
                'cover_image_path' => $coverPath,
                'audio_path' => $path,
                'spotify_url' => $entry['spotify_url'],
                'apple_podcasts_url' => $entry['apple_podcasts_url'],
                'soundcloud_url' => $entry['soundcloud_url'],
                'youtube_url' => $entry['youtube_url'],
                'sort_order' => $maxOrder,
            ]);
        }

        $primary = $podcast->fresh()->primaryTrack();
        if ($primary) {
            $podcast->audio_path = $primary->audio_path;
            $podcast->save();
        }
    }

    protected function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base !== '' ? $base : ('podcast-' . Str::random(6));
        $original = $slug;
        $i = 2;
        while (
            Podcast::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
