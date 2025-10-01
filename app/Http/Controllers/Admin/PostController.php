<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Post::with('category');
        
        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Search in titles
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title_ar', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('title_en', 'LIKE', '%' . $searchTerm . '%');
            });
        }
        
        $posts = $query->orderBy('created_at', 'desc')->paginate(15);
        $categories = Category::where('is_active', true)->get();
        
        return view('admin.posts.index', compact('posts', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.posts.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title_ar' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'content_ar' => 'required|string',
            'content_en' => 'nullable|string',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'background_image_ar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'background_image_en' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'featured_image_ar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'featured_image_en' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'files_ar.*.file' => 'nullable|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar|max:10240',
            'files_ar.*.display_name' => 'nullable|string|max:255',
            'files_en.*.file' => 'nullable|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar|max:10240',
            'files_en.*.display_name' => 'nullable|string|max:255',
            'status' => 'required|in:draft,under_review,published',
            'published_at' => 'nullable|date',
        ]);

        $post = new Post();
        $post->category_id = $request->category_id;
        $post->title_ar = $request->title_ar;
        $post->title_en = $request->title_en;
        // استخدم العنوان الإنجليزي إذا كان متوفراً، وإلا استخدم العربي
        $titleForSlug = $request->title_en ?: $request->title_ar;
        $post->slug = Str::slug($titleForSlug);
        $post->content_ar = $request->content_ar;
        $post->content_en = $request->content_en;
        $post->status = $request->status;
        $post->show_in_slider = $request->has('show_in_slider');
        $post->show_in_releases = $request->has('show_in_releases');
        $post->show_in_other_reports = $request->has('show_in_other_reports');
        $post->show_in_slider_ar = $request->has('show_in_slider_ar');
        $post->show_in_releases_ar = $request->has('show_in_releases_ar');
        $post->show_in_other_reports_ar = $request->has('show_in_other_reports_ar');
        $post->show_in_slider_en = $request->has('show_in_slider_en');
        $post->show_in_releases_en = $request->has('show_in_releases_en');
        $post->show_in_other_reports_en = $request->has('show_in_other_reports_en');
        $post->show_in_slider_ar = $request->has('show_in_slider_ar');
        $post->show_in_releases_ar = $request->has('show_in_releases_ar');
        $post->show_in_other_reports_ar = $request->has('show_in_other_reports_ar');
        $post->show_in_slider_en = $request->has('show_in_slider_en');
        $post->show_in_releases_en = $request->has('show_in_releases_en');
        $post->show_in_other_reports_en = $request->has('show_in_other_reports_en');
        $post->published_at = $request->published_at;
        $post->sort_order = $request->sort_order ?? 0;

        // Handle background image upload
        if ($request->hasFile('background_image')) {
            $backgroundImage = $request->file('background_image');
            $backgroundImagePath = $backgroundImage->store('posts/backgrounds', 'public');
            $post->background_image = $backgroundImagePath;
        }

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $featuredImage = $request->file('featured_image');
            $featuredImagePath = $featuredImage->store('posts/featured', 'public');
            $post->featured_image = $featuredImagePath;
        }

        // Handle Arabic background image upload
        if ($request->hasFile('background_image_ar')) {
            $backgroundImageAr = $request->file('background_image_ar');
            $backgroundImageArPath = $backgroundImageAr->store('posts/backgrounds/ar', 'public');
            $post->background_image_ar = $backgroundImageArPath;
        }

        // Handle English background image upload
        if ($request->hasFile('background_image_en')) {
            $backgroundImageEn = $request->file('background_image_en');
            $backgroundImageEnPath = $backgroundImageEn->store('posts/backgrounds/en', 'public');
            $post->background_image_en = $backgroundImageEnPath;
        }

        // Handle Arabic featured image upload
        if ($request->hasFile('featured_image_ar')) {
            $featuredImageAr = $request->file('featured_image_ar');
            $featuredImageArPath = $featuredImageAr->store('posts/featured/ar', 'public');
            $post->featured_image_ar = $featuredImageArPath;
        }

        // Handle English featured image upload
        if ($request->hasFile('featured_image_en')) {
            $featuredImageEn = $request->file('featured_image_en');
            $featuredImageEnPath = $featuredImageEn->store('posts/featured/en', 'public');
            $post->featured_image_en = $featuredImageEnPath;
        }

        $post->save();

        // Handle Arabic file uploads
        if ($request->has('files_ar')) {
            $filesData = $request->input('files_ar', []);
            foreach ($filesData as $index => $fileData) {
                $fileKey = "files_ar.{$index}.file";
                if ($request->hasFile($fileKey)) {
                    $file = $request->file($fileKey);
                    if ($file->isValid()) {
                        $displayName = $fileData['display_name'] ?? $file->getClientOriginalName();
                        
                        $fileName = time() . '_ar_' . $index . '_' . $file->getClientOriginalName();
                        $filePath = $file->storeAs('posts/files/ar', $fileName, 'public');
                        
                        $post->files()->create([
                            'file_path' => $filePath,
                            'original_name' => $file->getClientOriginalName(),
                            'display_name' => $displayName,
                            'file_size' => $file->getSize(),
                            'mime_type' => $file->getMimeType(),
                            'sort_order' => $index,
                            'language' => 'ar'
                        ]);
                    }
                }
            }
        }

        // Handle English file uploads
        if ($request->has('files_en')) {
            $filesData = $request->input('files_en', []);
            foreach ($filesData as $index => $fileData) {
                $fileKey = "files_en.{$index}.file";
                if ($request->hasFile($fileKey)) {
                    $file = $request->file($fileKey);
                    if ($file->isValid()) {
                        $displayName = $fileData['display_name'] ?? $file->getClientOriginalName();
                        
                        $fileName = time() . '_en_' . $index . '_' . $file->getClientOriginalName();
                        $filePath = $file->storeAs('posts/files/en', $fileName, 'public');
                        
                        $post->files()->create([
                            'file_path' => $filePath,
                            'original_name' => $file->getClientOriginalName(),
                            'display_name' => $displayName,
                            'file_size' => $file->getSize(),
                            'mime_type' => $file->getMimeType(),
                            'sort_order' => $index,
                            'language' => 'en'
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'تم إنشاء الموضوع بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.posts.edit', compact('post', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title_ar' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'content_ar' => 'required|string',
            'content_en' => 'nullable|string',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'background_image_ar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'background_image_en' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'featured_image_ar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'featured_image_en' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'files_ar.*.file' => 'nullable|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar|max:10240',
            'files_ar.*.display_name' => 'nullable|string|max:255',
            'files_en.*.file' => 'nullable|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar|max:10240',
            'files_en.*.display_name' => 'nullable|string|max:255',
            'status' => 'required|in:draft,under_review,published',
            'published_at' => 'nullable|date',
        ]);

        $post->category_id = $request->category_id;
        $post->title_ar = $request->title_ar;
        $post->title_en = $request->title_en;
        // استخدم العنوان الإنجليزي إذا كان متوفراً، وإلا استخدم العربي
        $titleForSlug = $request->title_en ?: $request->title_ar;
        $post->slug = Str::slug($titleForSlug);
        $post->content_ar = $request->content_ar;
        $post->content_en = $request->content_en;
        $post->status = $request->status;
        $post->show_in_slider = $request->has('show_in_slider');
        $post->show_in_releases = $request->has('show_in_releases');
        $post->show_in_other_reports = $request->has('show_in_other_reports');
        $post->show_in_slider_ar = $request->has('show_in_slider_ar');
        $post->show_in_releases_ar = $request->has('show_in_releases_ar');
        $post->show_in_other_reports_ar = $request->has('show_in_other_reports_ar');
        $post->show_in_slider_en = $request->has('show_in_slider_en');
        $post->show_in_releases_en = $request->has('show_in_releases_en');
        $post->show_in_other_reports_en = $request->has('show_in_other_reports_en');
        $post->published_at = $request->published_at;
        $post->sort_order = $request->sort_order ?? 0;

        // Handle background image upload
        if ($request->hasFile('background_image')) {
            // Delete old background image
            if ($post->background_image) {
                Storage::disk('public')->delete($post->background_image);
            }
            $backgroundImage = $request->file('background_image');
            $backgroundImagePath = $backgroundImage->store('posts/backgrounds', 'public');
            $post->background_image = $backgroundImagePath;
        } elseif ($request->has('remove_background_image')) {
            // Remove background image if requested
            if ($post->background_image) {
                Storage::disk('public')->delete($post->background_image);
                $post->background_image = null;
            }
        }

        // Handle Arabic background image upload
        if ($request->hasFile('background_image_ar')) {
            if ($post->background_image_ar) {
                Storage::disk('public')->delete($post->background_image_ar);
            }
            $backgroundImageAr = $request->file('background_image_ar');
            $backgroundImageArPath = $backgroundImageAr->store('posts/backgrounds/ar', 'public');
            $post->background_image_ar = $backgroundImageArPath;
        } elseif ($request->has('remove_background_image_ar')) {
            if ($post->background_image_ar) {
                Storage::disk('public')->delete($post->background_image_ar);
                $post->background_image_ar = null;
            }
        }

        // Handle English background image upload
        if ($request->hasFile('background_image_en')) {
            if ($post->background_image_en) {
                Storage::disk('public')->delete($post->background_image_en);
            }
            $backgroundImageEn = $request->file('background_image_en');
            $backgroundImageEnPath = $backgroundImageEn->store('posts/backgrounds/en', 'public');
            $post->background_image_en = $backgroundImageEnPath;
        } elseif ($request->has('remove_background_image_en')) {
            if ($post->background_image_en) {
                Storage::disk('public')->delete($post->background_image_en);
                $post->background_image_en = null;
            }
        }

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            // Delete old featured image
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $featuredImage = $request->file('featured_image');
            $featuredImagePath = $featuredImage->store('posts/featured', 'public');
            $post->featured_image = $featuredImagePath;
        }

        // Handle Arabic featured image upload
        if ($request->hasFile('featured_image_ar')) {
            if ($post->featured_image_ar) {
                Storage::disk('public')->delete($post->featured_image_ar);
            }
            $featuredImageAr = $request->file('featured_image_ar');
            $featuredImageArPath = $featuredImageAr->store('posts/featured/ar', 'public');
            $post->featured_image_ar = $featuredImageArPath;
        } elseif ($request->has('remove_featured_image_ar')) {
            if ($post->featured_image_ar) {
                Storage::disk('public')->delete($post->featured_image_ar);
                $post->featured_image_ar = null;
            }
        }

        // Handle English featured image upload
        if ($request->hasFile('featured_image_en')) {
            if ($post->featured_image_en) {
                Storage::disk('public')->delete($post->featured_image_en);
            }
            $featuredImageEn = $request->file('featured_image_en');
            $featuredImageEnPath = $featuredImageEn->store('posts/featured/en', 'public');
            $post->featured_image_en = $featuredImageEnPath;
        } elseif ($request->has('remove_featured_image_en')) {
            if ($post->featured_image_en) {
                Storage::disk('public')->delete($post->featured_image_en);
                $post->featured_image_en = null;
            }
        }

        $post->save();

        // Handle Arabic file uploads
        if ($request->has('files_ar')) {
            $filesData = $request->input('files_ar', []);
            foreach ($filesData as $index => $fileData) {
                $fileKey = "files_ar.{$index}.file";
                if ($request->hasFile($fileKey)) {
                    $file = $request->file($fileKey);
                    if ($file->isValid()) {
                        $displayName = $fileData['display_name'] ?? $file->getClientOriginalName();
                        
                        $fileName = time() . '_ar_' . $index . '_' . $file->getClientOriginalName();
                        $filePath = $file->storeAs('posts/files/ar', $fileName, 'public');
                        
                        $post->files()->create([
                            'file_path' => $filePath,
                            'original_name' => $file->getClientOriginalName(),
                            'display_name' => $displayName,
                            'file_size' => $file->getSize(),
                            'mime_type' => $file->getMimeType(),
                            'sort_order' => $index,
                            'language' => 'ar'
                        ]);
                    }
                }
            }
        }

        // Handle English file uploads
        if ($request->has('files_en')) {
            $filesData = $request->input('files_en', []);
            foreach ($filesData as $index => $fileData) {
                $fileKey = "files_en.{$index}.file";
                if ($request->hasFile($fileKey)) {
                    $file = $request->file($fileKey);
                    if ($file->isValid()) {
                        $displayName = $fileData['display_name'] ?? $file->getClientOriginalName();
                        
                        $fileName = time() . '_en_' . $index . '_' . $file->getClientOriginalName();
                        $filePath = $file->storeAs('posts/files/en', $fileName, 'public');
                        
                        $post->files()->create([
                            'file_path' => $filePath,
                            'original_name' => $file->getClientOriginalName(),
                            'display_name' => $displayName,
                            'file_size' => $file->getSize(),
                            'mime_type' => $file->getMimeType(),
                            'sort_order' => $index,
                            'language' => 'en'
                        ]);
                    }
                }
            }
        }

        // Handle file removal
        if ($request->has('remove_files')) {
            foreach ($request->remove_files as $fileId) {
                $fileToRemove = $post->files()->find($fileId);
                if ($fileToRemove) {
                    Storage::disk('public')->delete($fileToRemove->file_path);
                    $fileToRemove->delete();
                }
            }
        }

        return redirect()->route('admin.posts.edit', $post->id)
            ->with('success', 'تم تحديث الموضوع بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        // Delete associated files
        if ($post->background_image) {
            Storage::disk('public')->delete($post->background_image);
        }
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }
        
        // Delete multilingual images
        if ($post->background_image_ar) {
            Storage::disk('public')->delete($post->background_image_ar);
        }
        if ($post->background_image_en) {
            Storage::disk('public')->delete($post->background_image_en);
        }
        if ($post->featured_image_ar) {
            Storage::disk('public')->delete($post->featured_image_ar);
        }
        if ($post->featured_image_en) {
            Storage::disk('public')->delete($post->featured_image_en);
        }
        
        // Delete multiple files
        foreach ($post->files as $file) {
            Storage::disk('public')->delete($file->file_path);
        }

        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'تم حذف الموضوع بنجاح');
    }
}
