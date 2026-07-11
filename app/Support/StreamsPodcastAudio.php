<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait StreamsPodcastAudio
{
    /**
     * بث ملف صوت مع دعم HTTP Range حتى يعمل شريط التقديم/التأخير.
     */
    protected function streamAudioFile(string $relativePath, Request $request): StreamedResponse
    {
        $path = Storage::disk('public')->path($relativePath);
        if (!is_file($path)) {
            abort(404);
        }

        $size = filesize($path);
        $mime = function_exists('mime_content_type') ? (mime_content_type($path) ?: 'audio/mpeg') : 'audio/mpeg';

        $rangeHeader = $request->header('Range');
        $start = 0;
        $end = $size > 0 ? $size - 1 : 0;
        $status = 200;
        $headers = [
            'Content-Type' => $mime,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=0',
        ];

        if ($rangeHeader && preg_match('/bytes=(\d*)-(\d*)/i', $rangeHeader, $matches)) {
            if ($matches[1] !== '') {
                $start = (int) $matches[1];
            }
            if ($matches[2] !== '') {
                $end = (int) $matches[2];
            }
            $start = max(0, $start);
            $end = min($end, max(0, $size - 1));

            if ($size === 0 || $start > $end || $start >= $size) {
                return response()->stream(function () {}, 416, [
                    'Content-Range' => "bytes */{$size}",
                    'Accept-Ranges' => 'bytes',
                ]);
            }

            $status = 206;
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
        }

        $headers['Content-Length'] = $end - $start + 1;

        return response()->stream(function () use ($path, $start, $end) {
            $chunkSize = 8192 * 8;
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
                    $read = min($chunkSize, $bytesToOutput);
                    $buffer = fread($fh, $read);
                    if ($buffer === false || $buffer === '') {
                        break;
                    }
                    echo $buffer;
                    if (function_exists('flush')) {
                        flush();
                    }
                    $bytesToOutput -= strlen($buffer);
                }
            } finally {
                fclose($fh);
            }
        }, $status, $headers);
    }
}
