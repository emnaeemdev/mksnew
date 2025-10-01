<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display posts by category or all posts with optional category filter
     */
    public function index(Request $request)
    {
        $categorySlug = $request->get('category');
        $category = null;
        
        $query = Post::published()->with('category');
        
        if ($categorySlug) {
            $category = Category::where('slug', $categorySlug)->firstOrFail();
            $query->where('category_id', $category->id);
        }
        
        // Ensure English locale only shows posts with English translation
        if (app()->getLocale() === 'en') {
            $query->whereNotNull('title_en')->where('title_en', '!=', '')
                  ->whereNotNull('content_en')->where('content_en', '!=', '');
        }
        
        $posts = $query->orderBy('sort_order')
                       ->orderBy('published_at', 'desc')
                       ->paginate(12);
        
        $categories = Category::where('is_active', true)
                             ->orderBy('sort_order')
                             ->get();
        
        return view('frontend.posts.index', compact('posts', 'categories', 'category'));
    }
    
    /**
     * Display posts by category
     */
    public function category($locale, $category)
    {
        // Find category by slug
        $categoryModel = Category::where('slug', $category)->firstOrFail();
        
        $query = Post::where('category_id', $categoryModel->id)
                    ->published()
                    ->with('category');
        
        // Ensure English locale only shows posts with English translation
        if (app()->getLocale() === 'en') {
            $query->whereNotNull('title_en')->where('title_en', '!=', '')
                  ->whereNotNull('content_en')->where('content_en', '!=', '');
        }
        
        $posts = $query->orderBy('sort_order')
                       ->orderBy('published_at', 'desc')
                       ->paginate(12);
        
        // Get all categories for navigation
        $categories = Category::where('is_active', true)
                             ->orderBy('sort_order')
                             ->get();
        
        return view('frontend.posts.index', compact('posts', 'categories') + ['category' => $categoryModel]);
    }

    /**
     * Display a single post
     */
    public function show($locale, $category, $slug)
    {
        // Find category by slug
        $categoryModel = Category::where('slug', $category)->firstOrFail();
        
        $post = Post::where('slug', $slug)
                   ->where('category_id', $categoryModel->id)
                   ->published()
                   ->with('category')
                   ->firstOrFail();
        
        // If current locale is English and no English translation is available, show message and redirect
        if (app()->getLocale() === 'en' && !$post->hasEnglishVersion()) {
            return view('frontend.no-translation');
        }
        
        // Increment views count
        $post->incrementViews();
        
        // Get other reports posts based on current language
        $currentLocale = app()->getLocale();
        if ($currentLocale === 'ar') {
            $otherReportsPosts = Post::published()
                                    ->forOtherReportsAr()
                                    ->where('id', '!=', $post->id)
                                    ->orderBy('sort_order')
                                    ->orderBy('published_at', 'desc')
                                    ->limit(6)
                                    ->get();
        } else {
            $otherReportsPosts = Post::published()
                                    ->forOtherReportsEn()
                                    ->where('id', '!=', $post->id)
                                    ->orderBy('sort_order')
                                    ->orderBy('published_at', 'desc')
                                    ->limit(6)
                                    ->get();
        }
        
        // Get IDs of other reports posts to exclude from related posts
        // Include posts that show in other reports for ANY language to prevent duplication
        $otherReportsIds = $otherReportsPosts->pluck('id')->toArray();
        $allOtherReportsIds = Post::published()
            ->where(function($query) {
                $query->where('show_in_other_reports_ar', true)
                      ->orWhere('show_in_other_reports_en', true);
            })
            ->where('id', '!=', $post->id)
            ->pluck('id')
            ->toArray();
        $otherReportsIds = array_unique(array_merge($otherReportsIds, $allOtherReportsIds));
        
        // Get related posts from the same category, excluding other reports posts
        $relatedPostsQuery = Post::where('category_id', $post->category_id)
                                ->where('id', '!=', $post->id)
                                ->whereNotIn('id', $otherReportsIds)
                                ->published();
        
        // Ensure English locale only shows posts with English translation
        if (app()->getLocale() === 'en') {
            $relatedPostsQuery->whereNotNull('title_en')->where('title_en', '!=', '')
                             ->whereNotNull('content_en')->where('content_en', '!=', '');
        }
        
        $relatedPosts = $relatedPostsQuery->orderBy('published_at', 'desc')
                                         ->limit(6)
                                         ->get();
        
        // Get all categories for navigation
        $categories = Category::where('is_active', true)
                             ->orderBy('sort_order')
                             ->get();
        
        return view('frontend.posts.show', compact('post', 'relatedPosts', 'otherReportsPosts', 'categories'));
    }
    
    /**
     * Search posts
     */
    public function search(Request $request, $locale = null)
    {
        $queryText = $request->get('q');
        $categorySlug = $request->get('category');
        $category = null;
        
        if ($categorySlug) {
            $category = Category::where('slug', $categorySlug)->first();
        }
        
        $query = Post::published()
                    ->when($queryText, function($q) use ($queryText) {
                        $q->where(function($qq) use ($queryText) {
                            $qq->where('title', 'like', '%' . $queryText . '%')
                               ->orWhere('content', 'like', '%' . $queryText . '%');
                        });
                    })
                    ->when($categorySlug, function($q) use ($categorySlug) {
                        $q->whereHas('category', function($cat) use ($categorySlug) {
                            $cat->where('slug', $categorySlug);
                        });
                    });
        
        // Ensure English locale only shows posts with English translation
        if (app()->getLocale() === 'en') {
            $query->whereNotNull('title_en')->where('title_en', '!=', '')
                  ->whereNotNull('content_en')->where('content_en', '!=', '');
        }
        
        $posts = $query->orderBy('published_at', 'desc')
                       ->paginate(12);
        
        $categories = Category::where('is_active', true)
                             ->orderBy('sort_order')
                             ->get();
        
        return view('frontend.posts.index', compact('posts', 'categories', 'category') + ['searchQuery' => $queryText]);
    }
    
    /**
     * Get slider posts for homepage
     */
    public function getSliderPosts()
    {
        return Post::published()
                  ->forSlider()
                  ->orderBy('sort_order')
                  ->orderBy('published_at', 'desc')
                  ->limit(5)
                  ->get();
    }
    
    /**
     * Get release posts for homepage
     */
    public function getReleasePosts()
    {
        return Post::published()
                  ->forReleases()
                  ->orderBy('sort_order')
                  ->orderBy('published_at', 'desc')
                  ->limit(8)
                  ->get();
    }
    
    /**
     * Download post file
     */
    public function downloadFile($locale, $category, $slug)
    {
        // Find category by slug
        $categoryModel = Category::where('slug', $category)->firstOrFail();
        
        $post = Post::where('slug', $slug)
                   ->where('category_id', $categoryModel->id)
                   ->published()
                   ->firstOrFail();
        
        if (!$post->hasFile()) {
            abort(404);
        }
        
        $filePath = storage_path('app/public/' . $post->file_path);
        
        if (!file_exists($filePath)) {
            abort(404);
        }
        
        return response()->download($filePath, $post->file_name);
    }
}
