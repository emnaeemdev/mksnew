<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Media::with('uploader');

        // Filter by file type
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        // Search by name or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        $media = $query->latest()->paginate(20);
        
        return view('admin.media.index', compact('media'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.media.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
$request->validate([
    'files.*' => 'nullable|mimes:jpg,jpeg,png,gif,svg,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar|max:10240',
    'alt_text.*' => 'nullable|string|max:255',
    'description.*' => 'nullable|string'
]);


        $uploadedFiles = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $index => $file) {
                $originalName = $file->getClientOriginalName();
                $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('media', $fileName, 'public');

                $media = Media::create([
                    'name' => pathinfo($originalName, PATHINFO_FILENAME),
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'alt_text' => $request->input("alt_text.{$index}"),
                    'description' => $request->input("description.{$index}"),
                    'uploaded_by' => auth()->id() ?? 1
                ]);

                $uploadedFiles[] = $media;
            }
        }

        return redirect()->route('admin.media.index')
            ->with('success', 'تم رفع ' . count($uploadedFiles) . ' ملف بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Media $medium)
    {
        $medium->load('uploader');
        return view('admin.media.show', compact('medium'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Media $medium)
    {
        return view('admin.media.edit', compact('medium'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Media $medium)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'description' => 'nullable|string'
        ]);

        $medium->update([
            'name' => $request->name,
            'alt_text' => $request->alt_text,
            'description' => $request->description
        ]);

        return redirect()->route('admin.media.index')
            ->with('success', 'تم تحديث الملف بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Media $medium)
    {
        // Delete the file from storage
        if (Storage::disk('public')->exists($medium->file_path)) {
            Storage::disk('public')->delete($medium->file_path);
        }

        // Delete the database record
        $medium->delete();

        return redirect()->route('admin.media.index')
            ->with('success', 'تم حذف الملف بنجاح');
    }

    /**
     * Download the media file.
     */
    public function download(Media $medium)
    {
        if (!Storage::disk('public')->exists($medium->file_path)) {
            abort(404, 'الملف غير موجود');
        }

        return Storage::disk('public')->download($medium->file_path, $medium->file_name);
    }

    /**
     * Bulk delete media files.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'media_ids' => 'required|array',
            'media_ids.*' => 'exists:media,id'
        ]);

        $media = Media::whereIn('id', $request->media_ids)->get();
        
        foreach ($media as $item) {
            // Delete file from storage
            if (Storage::disk('public')->exists($item->file_path)) {
                Storage::disk('public')->delete($item->file_path);
            }
            
            // Delete database record
            $item->delete();
        }

        return redirect()->route('admin.media.index')
            ->with('success', 'تم حذف ' . count($media) . ' ملف بنجاح');
    }
}