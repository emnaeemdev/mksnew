<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PodcastController extends Controller
{
    public function index(Request $request)
    {
        $query = Podcast::query();

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where('title', 'LIKE', "%$q%")
                  ->orWhere('content', 'LIKE', "%$q%");
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
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:podcasts,slug',
            'content' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'audio_file' => 'nullable|mimes:mp3,wav,ogg,m4a,aac|max:20480',
            'spotify_url' => 'nullable|url|max:255',
            'apple_podcasts_url' => 'nullable|url|max:255',
            'soundcloud_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'published_at' => 'nullable|date',
            'is_published' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $podcast = new Podcast();
        $podcast->title = $data['title'];
        $podcast->slug = $data['slug'] ?? Str::slug($data['title']);
        $podcast->content = $data['content'] ?? null;
        $podcast->spotify_url = $data['spotify_url'] ?? null;
        $podcast->apple_podcasts_url = $data['apple_podcasts_url'] ?? null;
        $podcast->soundcloud_url = $data['soundcloud_url'] ?? null;
        $podcast->youtube_url = $data['youtube_url'] ?? null;
        $podcast->published_at = $data['published_at'] ?? null;
        $podcast->is_published = (bool)($data['is_published'] ?? false);
        $podcast->sort_order = $data['sort_order'] ?? 0;

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('podcasts/covers', 'public');
            $podcast->cover_image_path = $path;
        }
        if ($request->hasFile('audio_file')) {
            $path = $request->file('audio_file')->store('podcasts/audio', 'public');
            $podcast->audio_path = $path;
        }

        $podcast->save();

        return redirect()->route('admin.podcasts.index')->with('success', 'تم إنشاء البودكاست بنجاح');
    }

    public function show(Podcast $podcast)
    {
        return view('admin.podcasts.show', compact('podcast'));
    }

    public function edit(Podcast $podcast)
    {
        return view('admin.podcasts.edit', compact('podcast'));
    }

    public function update(Request $request, Podcast $podcast)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:podcasts,slug,' . $podcast->id,
            'content' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'audio_file' => 'nullable|mimes:mp3,wav,ogg,m4a,aac|max:20480',
            'spotify_url' => 'nullable|url|max:255',
            'apple_podcasts_url' => 'nullable|url|max:255',
            'soundcloud_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'published_at' => 'nullable|date',
            'is_published' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $podcast->title = $data['title'];
        $podcast->slug = $data['slug'] ?? $podcast->slug;
        $podcast->content = $data['content'] ?? null;
        $podcast->spotify_url = $data['spotify_url'] ?? null;
        $podcast->apple_podcasts_url = $data['apple_podcasts_url'] ?? null;
        $podcast->soundcloud_url = $data['soundcloud_url'] ?? null;
        $podcast->youtube_url = $data['youtube_url'] ?? null;
        $podcast->published_at = $data['published_at'] ?? null;
        $podcast->is_published = (bool)($data['is_published'] ?? false);
        $podcast->sort_order = $data['sort_order'] ?? 0;

        if ($request->hasFile('cover_image')) {
            if ($podcast->cover_image_path) {
                Storage::disk('public')->delete($podcast->cover_image_path);
            }
            $path = $request->file('cover_image')->store('podcasts/covers', 'public');
            $podcast->cover_image_path = $path;
        }
        if ($request->hasFile('audio_file')) {
            if ($podcast->audio_path) {
                Storage::disk('public')->delete($podcast->audio_path);
            }
            $path = $request->file('audio_file')->store('podcasts/audio', 'public');
            $podcast->audio_path = $path;
        }

        $podcast->save();

        return redirect()->route('admin.podcasts.index')->with('success', 'تم تحديث البودكاست بنجاح');
    }

    public function destroy(Podcast $podcast)
    {
        if ($podcast->cover_image_path) {
            Storage::disk('public')->delete($podcast->cover_image_path);
        }
        if ($podcast->audio_path) {
            Storage::disk('public')->delete($podcast->audio_path);
        }
        $podcast->delete();
        return redirect()->route('admin.podcasts.index')->with('success', 'تم حذف البودكاست بنجاح');
    }
}