<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SecureUploadService
{
    /**
     * MIME type => safe extension map (server-controlled only).
     */
    public const ALLOWED_MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'text/plain' => 'txt',
        'application/zip' => 'zip',
        'application/x-zip-compressed' => 'zip',
        'application/x-rar-compressed' => 'rar',
        'application/vnd.rar' => 'rar',
        'audio/mpeg' => 'mp3',
        'audio/mp3' => 'mp3',
        'audio/wav' => 'wav',
        'audio/x-wav' => 'wav',
        'audio/ogg' => 'ogg',
        'audio/mp4' => 'm4a',
        'audio/x-m4a' => 'm4a',
        'audio/aac' => 'aac',
    ];

    public const DANGEROUS_EXTENSIONS = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'pht',
        'exe', 'bat', 'cmd', 'com', 'msi', 'scr',
        'js', 'jsp', 'asp', 'aspx', 'cgi', 'pl', 'py', 'rb',
        'sh', 'bash', 'htaccess', 'htpasswd', 'ini', 'svg',
    ];

    /**
     * Store an uploaded file with a server-generated name and a safe extension
     * derived from the detected MIME type (never from the client filename).
     */
    public function store(UploadedFile $file, string $directory, string $disk = 'public', ?array $allowedMimes = null): array
    {
        if (!$file->isValid()) {
            throw new InvalidArgumentException('Invalid upload.');
        }

        $mime = strtolower((string) ($file->getMimeType() ?: ''));
        $allowed = $allowedMimes ?? array_keys(self::ALLOWED_MIME_EXTENSIONS);

        if ($mime === '' || !in_array($mime, $allowed, true)) {
            throw new InvalidArgumentException('File type is not allowed.');
        }

        $extension = self::ALLOWED_MIME_EXTENSIONS[$mime]
            ?? $this->safeExtensionFromMime($mime);

        if ($extension === null || in_array($extension, self::DANGEROUS_EXTENSIONS, true)) {
            throw new InvalidArgumentException('File extension is not allowed.');
        }

        $safeName = Str::uuid()->toString() . '.' . $extension;
        $path = trim($directory, '/') . '/' . $safeName;

        Storage::disk($disk)->putFileAs(trim($directory, '/'), $file, $safeName);

        return [
            'path' => $path,
            'stored_name' => $safeName,
            'original_name' => $this->sanitizeOriginalName($file->getClientOriginalName()),
            'mime_type' => $mime,
            'size' => $file->getSize() ?: 0,
            'extension' => $extension,
        ];
    }

    public function sanitizeOriginalName(?string $name): string
    {
        $name = basename((string) $name);
        $name = preg_replace('/[^\pL\pN\.\-\_\s\(\)\[\]]+/u', '_', $name) ?: 'file';
        $name = trim($name, '._ ');

        $parts = explode('.', $name);
        if (count($parts) > 1) {
            $ext = strtolower((string) array_pop($parts));
            if (in_array($ext, self::DANGEROUS_EXTENSIONS, true)) {
                $name = implode('.', $parts) . '.bin';
            }
        }

        return Str::limit($name !== '' ? $name : 'file', 180, '');
    }

    public function sanitizeDisplayName(?string $name, ?string $fallback = null): string
    {
        $name = trim(strip_tags((string) ($name ?: $fallback ?: 'file')));
        $name = preg_replace('/[^\pL\pN\.\-\_\s\(\)\[\]]+/u', ' ', $name) ?: 'file';

        return Str::limit(trim($name), 180, '');
    }

    protected function safeExtensionFromMime(string $mime): ?string
    {
        return self::ALLOWED_MIME_EXTENSIONS[$mime] ?? null;
    }
}
