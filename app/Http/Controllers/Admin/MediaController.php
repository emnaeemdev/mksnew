<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\SecureUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = Media::with('uploader');

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        $media = $query->latest()->paginate(20);

        return view('admin.media.index', compact('media'));
    }

    public function create()
    {
        return view('admin.media.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => 'file|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar|max:40240',
            'alt_text.*' => 'nullable|string|max:255',
            'description.*' => 'nullable|string|max:2000',
        ], [
            'files.*.mimes' => 'نوع الملف غير مسموح به',
            'files.*.max' => 'حجم الملف كبير جدًا',
        ]);

        $uploader = app(SecureUploadService::class);
        $uploadedFiles = [];

        foreach ($request->file('files', []) as $index => $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            try {
                $stored = $uploader->store($file, 'media', 'public');
            } catch (\InvalidArgumentException $e) {
                continue;
            }

            $media = Media::create([
                'name' => pathinfo($stored['original_name'], PATHINFO_FILENAME) ?: 'file',
                'file_name' => $stored['stored_name'],
                'file_path' => $stored['path'],
                'file_size' => $stored['size'],
                'mime_type' => $stored['mime_type'],
                'alt_text' => $request->input("alt_text.{$index}"),
                'description' => $request->input("description.{$index}"),
                'uploaded_by' => auth()->id(),
            ]);

            $uploadedFiles[] = $media;
        }

        if (!empty($uploadedFiles)) {
            return redirect()->route('admin.media.edit', end($uploadedFiles))
                ->with('success', 'تم رفع ' . count($uploadedFiles) . ' ملف بنجاح');
        }

        return redirect()->route('admin.media.index')
            ->with('error', 'لم يتم رفع أي ملفات صالحة');
    }

    public function show(Media $medium)
    {
        $medium->load('uploader');

        return view('admin.media.show', compact('medium'));
    }

    public function edit(Media $medium)
    {
        return view('admin.media.edit', compact('medium'));
    }

    public function update(Request $request, Media $medium)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        $medium->update([
            'name' => $request->name,
            'alt_text' => $request->alt_text,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.media.index')
            ->with('success', 'تم تحديث الملف بنجاح');
    }

    public function destroy(Media $medium)
    {
        if ($medium->file_path && Storage::disk('public')->exists($medium->file_path)) {
            Storage::disk('public')->delete($medium->file_path);
        }

        $medium->delete();

        return redirect()->route('admin.media.index')
            ->with('success', 'تم حذف الملف بنجاح');
    }

    public function download(Media $medium)
    {
        if (!Storage::disk('public')->exists($medium->file_path)) {
            abort(404, 'الملف غير موجود');
        }

        $downloadName = app(SecureUploadService::class)->sanitizeOriginalName($medium->file_name ?: 'file');

        return Storage::disk('public')->download($medium->file_path, $downloadName);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'media_ids' => 'required|array',
            'media_ids.*' => 'exists:media,id',
        ]);

        $media = Media::whereIn('id', $request->media_ids)->get();

        foreach ($media as $item) {
            if ($item->file_path && Storage::disk('public')->exists($item->file_path)) {
                Storage::disk('public')->delete($item->file_path);
            }
            $item->delete();
        }

        return redirect()->route('admin.media.index')
            ->with('success', 'تم حذف ' . count($media) . ' ملف بنجاح');
    }
}
