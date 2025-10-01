<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PodcastController extends Controller
{
    /**
     * Display a listing of the podcasts.
     */
    public function index(Request $request)
    {
        $query = Podcast::query()->where('is_published', true);

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

    /**
     * Display the specified podcast by ID or slug.
     */
    public function show($locale, Podcast $podcast)
    {
        if (!$podcast->is_published) {
            abort(404);
        }

        $title = $podcast->title;
        $description = strip_tags(mb_substr($podcast->content ?? '', 0, 160));

        return view('frontend.podcasts.show', compact('podcast', 'title', 'description'));
    }

    /**
     * Stream the podcast audio with HTTP Range support for seeking.
     */
    public function stream($locale, Podcast $podcast, Request $request)
    {
        if (!$podcast->is_published || !$podcast->audio_path) {
            abort(404);
        }

        $path = Storage::disk('public')->path($podcast->audio_path);
        if (!is_file($path)) {
            abort(404);
        }

        $size = filesize($path);
        $mime = function_exists('mime_content_type') ? (mime_content_type($path) ?: 'audio/mpeg') : 'audio/mpeg';

        $rangeHeader = $request->header('Range');
        $start = 0;
        $end = $size - 1;
        $status = 200;
        $headers = [
            'Content-Type' => $mime,
            'Accept-Ranges' => 'bytes',
        ];

        if ($rangeHeader && preg_match('/bytes=(\d*)-(\d*)/i', $rangeHeader, $matches)) {
            if ($matches[1] !== '') {
                $start = (int) $matches[1];
            }
            if ($matches[2] !== '') {
                $end = (int) $matches[2];
            }
            $start = max(0, $start);
            $end = min($end, $size - 1);

            if ($start > $end || $start >= $size) {
                return response('', 416, [
                    'Content-Range' => "bytes */{$size}",
                    'Accept-Ranges' => 'bytes',
                ]);
            }

            $status = 206; // Partial Content
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
        }

        $length = $end - $start + 1;
        $headers['Content-Length'] = $length;
        $headers['Cache-Control'] = 'public, max-age=0';

        return response()->stream(function () use ($path, $start, $end) {
            $chunkSize = 8192;
            $fh = fopen($path, 'rb');
            if (!$fh) {
                return;
            }
            try {
                if ($start > 0) {
                    fseek($fh, $start);
                }
                $bytesToOutput = $end - $start + 1;
                while ($bytesToOutput > 0 && !feof($fh)) {
                    $read = ($bytesToOutput > $chunkSize) ? $chunkSize : $bytesToOutput;
                    $buffer = fread($fh, $read);
                    if ($buffer === false) {
                        break;
                    }
                    echo $buffer;
                    flush();
                    $bytesToOutput -= strlen($buffer);
                }
            } finally {
                fclose($fh);
            }
        }, $status, $headers);
    }
}