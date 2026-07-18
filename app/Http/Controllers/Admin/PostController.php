<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Services\HtmlSanitizer;
use App\Services\SecureUploadService;
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
        $selectedCategory = $request->filled('category')
            ? Category::find($request->category)
            : null;

        return view('admin.posts.index', compact('posts', 'categories', 'selectedCategory'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $selectedCategoryId = $request->get('category');

        return view('admin.posts.create', compact('categories', 'selectedCategoryId'));
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
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200',
            'background_image_ar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200',
            'background_image_en' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200',
            'featured_image_ar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200',
            'featured_image_en' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200',
            'files_ar.*.file' => 'nullable|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar|max:51200',
            'files_ar.*.display_name' => 'nullable|string|max:255',
            'files_en.*.file' => 'nullable|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar|max:51200',
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
        $sanitizer = app(HtmlSanitizer::class);
        $post->content_ar = $sanitizer->clean($request->content_ar);
        $post->content_en = $sanitizer->clean($request->content_en);
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

        $this->storePostAttachmentFiles($request, $post, 'ar');
        $this->storePostAttachmentFiles($request, $post, 'en');

        $post->syncKeywordNames($request->input('keywords'));

        return redirect()->route('admin.posts.edit', $post)
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
        $post->load('keywords');
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
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200',
            'background_image_ar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200',
            'background_image_en' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200',
            'featured_image_ar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200',
            'featured_image_en' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200',
            'files_ar.*.file' => 'nullable|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar|max:51200',
            'files_ar.*.display_name' => 'nullable|string|max:255',
            'files_en.*.file' => 'nullable|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar|max:51200',
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
        $sanitizer = app(HtmlSanitizer::class);
        $post->content_ar = $sanitizer->clean($request->content_ar);
        $post->content_en = $sanitizer->clean($request->content_en);
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
            $legacyBackground = $post->getRawOriginal('background_image');
            if ($legacyBackground) {
                Storage::disk('public')->delete($legacyBackground);
            }
            $backgroundImage = $request->file('background_image');
            $backgroundImagePath = $backgroundImage->store('posts/backgrounds', 'public');
            $post->background_image = $backgroundImagePath;
        } elseif ($request->boolean('remove_background_image')) {
            $this->removeLegacyImage($post, 'background_image');
        }

        // Handle Arabic background image upload
        if ($request->hasFile('background_image_ar')) {
            $this->deleteStoredImage($post->getRawOriginal('background_image_ar'));
            $backgroundImageArPath = $request->file('background_image_ar')->store('posts/backgrounds/ar', 'public');
            $post->background_image_ar = $backgroundImageArPath;
            $this->clearLegacyImageForLocale($post, 'background_image', 'backgrounds/ar');
        } elseif ($request->boolean('remove_background_image_ar')) {
            $this->removeLocaleImage($post, 'background_image_ar', 'background_image', 'backgrounds/ar');
        }

        // Handle English background image upload
        if ($request->hasFile('background_image_en')) {
            $this->deleteStoredImage($post->getRawOriginal('background_image_en'));
            $backgroundImageEnPath = $request->file('background_image_en')->store('posts/backgrounds/en', 'public');
            $post->background_image_en = $backgroundImageEnPath;
            $this->clearLegacyImageForLocale($post, 'background_image', 'backgrounds/en');
        } elseif ($request->boolean('remove_background_image_en')) {
            $this->removeLocaleImage($post, 'background_image_en', 'background_image', 'backgrounds/en');
        }

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $this->deleteStoredImage($post->getRawOriginal('featured_image'));
            $featuredImagePath = $request->file('featured_image')->store('posts/featured', 'public');
            $post->featured_image = $featuredImagePath;
        }

        // Handle Arabic featured image upload
        if ($request->hasFile('featured_image_ar')) {
            $this->deleteStoredImage($post->getRawOriginal('featured_image_ar'));
            $featuredImageArPath = $request->file('featured_image_ar')->store('posts/featured/ar', 'public');
            $post->featured_image_ar = $featuredImageArPath;
            $this->clearLegacyImageForLocale($post, 'featured_image', 'featured/ar');
        } elseif ($request->boolean('remove_featured_image_ar')) {
            $this->removeLocaleImage($post, 'featured_image_ar', 'featured_image', 'featured/ar');
        }

        // Handle English featured image upload
        if ($request->hasFile('featured_image_en')) {
            $this->deleteStoredImage($post->getRawOriginal('featured_image_en'));
            $featuredImageEnPath = $request->file('featured_image_en')->store('posts/featured/en', 'public');
            $post->featured_image_en = $featuredImageEnPath;
            $this->clearLegacyImageForLocale($post, 'featured_image', 'featured/en');
        } elseif ($request->boolean('remove_featured_image_en')) {
            $this->removeLocaleImage($post, 'featured_image_en', 'featured_image', 'featured/en');
        }

        $post->save();

        if ($request->has('existing_file_names')) {
            foreach ($request->input('existing_file_names', []) as $fileId => $displayName) {
                $post->files()->where('id', (int) $fileId)->update([
                    'display_name' => trim((string) $displayName),
                ]);
            }
        }

        $this->storePostAttachmentFiles($request, $post, 'ar');
        $this->storePostAttachmentFiles($request, $post, 'en');

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

        $post->syncKeywordNames($request->input('keywords'));

        return redirect()->route('admin.posts.edit', $post->id)
            ->with('success', 'تم تحديث الموضوع بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        foreach (['background_image', 'featured_image', 'background_image_ar', 'background_image_en', 'featured_image_ar', 'featured_image_en'] as $column) {
            $this->deleteStoredImage($post->getRawOriginal($column));
        }

        // Delete multiple files
        foreach ($post->files as $file) {
            Storage::disk('public')->delete($file->file_path);
        }

        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'تم حذف الموضوع بنجاح');
    }

    private function storePostAttachmentFiles(Request $request, Post $post, string $language): void
    {
        $inputKey = $language === 'en' ? 'files_en' : 'files_ar';
        if (!$request->has($inputKey)) {
            return;
        }

        $uploader = app(SecureUploadService::class);
        $filesData = $request->input($inputKey, []);

        foreach ($filesData as $index => $fileData) {
            $fileKey = "{$inputKey}.{$index}.file";
            if (!$request->hasFile($fileKey)) {
                continue;
            }

            $file = $request->file($fileKey);
            if (!$file || !$file->isValid()) {
                continue;
            }

            try {
                $stored = $uploader->store($file, "posts/files/{$language}", 'public');
            } catch (\InvalidArgumentException $e) {
                continue;
            }

            $post->files()->create([
                'file_path' => $stored['path'],
                'original_name' => $stored['original_name'],
                'display_name' => $uploader->sanitizeDisplayName($fileData['display_name'] ?? null, $stored['original_name']),
                'file_size' => $stored['size'],
                'mime_type' => $stored['mime_type'],
                'sort_order' => $index,
                'language' => $language,
            ]);
        }
    }

    private function deleteStoredImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function removeLegacyImage(Post $post, string $column): void
    {
        $this->deleteStoredImage($post->getRawOriginal($column));
        $post->{$column} = null;
    }

    private function removeLocaleImage(Post $post, string $localeColumn, string $legacyColumn, string $localeFolder): void
    {
        $localePath = $post->getRawOriginal($localeColumn);
        $this->deleteStoredImage($localePath);
        $post->{$localeColumn} = null;

        $legacyPath = $post->getRawOriginal($legacyColumn);
        if (!$legacyPath) {
            return;
        }

        if ($legacyPath === $localePath || str_contains($legacyPath, $localeFolder)) {
            if ($legacyPath !== $localePath) {
                $this->deleteStoredImage($legacyPath);
            }
            $post->{$legacyColumn} = null;
        }
    }

    private function clearLegacyImageForLocale(Post $post, string $legacyColumn, string $localeFolder): void
    {
        $legacyPath = $post->getRawOriginal($legacyColumn);
        if ($legacyPath && str_contains($legacyPath, $localeFolder)) {
            $this->deleteStoredImage($legacyPath);
            $post->{$legacyColumn} = null;
        }
    }
}
