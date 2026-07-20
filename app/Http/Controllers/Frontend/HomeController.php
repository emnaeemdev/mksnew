<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\DocumentSection;
use Illuminate\Http\Request;
use App\Models\NewsletterSubscription;

class HomeController extends Controller
{
    /**
     * Display the homepage
     */
    public function index($locale = null)
    {
        return $this->renderHomePage('frontend.home');
    }

    /**
     * تصاميم تجريبية للصفحة الرئيسية — بدون مبدّل تصميم في الواجهة.
     * افتح: /ar/home1 أو /ar/home2
     */
    public function home1($locale = null)
    {
        return $this->renderHomePage('frontend.home1', lockTemplate: true);
    }

    public function home2($locale = null)
    {
        return $this->renderHomePage('frontend.home2', lockTemplate: true);
    }

    public function home3($locale = null)
    {
        return $this->renderHomePage('frontend.home3', lockTemplate: true);
    }

    private function renderHomePage(string $viewTemplate, bool $lockTemplate = false)
    {
        $currentLocale = app()->getLocale();
        
        // Get slider posts based on current language
        if ($currentLocale === 'ar') {
            $sliderPosts = Post::published()
                              ->forSliderAr()
                              ->orderBy('sort_order')
                              ->orderBy('published_at', 'desc')
                              ->limit(5)
                              ->get();
        } else {
            $sliderPosts = Post::published()
                              ->forSliderEn()
                              ->orderBy('sort_order')
                              ->orderBy('published_at', 'desc')
                              ->limit(5)
                              ->get();
        }
        
        // Get release posts based on current language
        if ($currentLocale === 'ar') {
            $releasePosts = Post::published()
                               ->forReleasesAr()
                               ->orderBy('sort_order')
                               ->orderBy('published_at', 'desc')
                               ->limit(5)
                               ->get();
        } else {
            $releasePosts = Post::published()
                               ->forReleasesEn()
                               ->orderBy('sort_order')
                               ->orderBy('published_at', 'desc')
                               ->limit(4)
                               ->get();
        }
        
        // Get other reports posts based on current language
        if ($currentLocale === 'ar') {
            $otherReportsPosts = Post::published()
                                    ->forOtherReportsAr()
                                    ->orderBy('sort_order')
                                    ->orderBy('published_at', 'desc')
                                    ->limit(6)
                                    ->get();
        } else {
            $otherReportsPosts = Post::published()
                                    ->forOtherReportsEn()
                                    ->orderBy('sort_order')
                                    ->orderBy('published_at', 'desc')
                                    ->limit(6)
                                    ->get();
        }
        
        // Get latest posts (all published posts regardless of language)
        if ($currentLocale === 'en') {
            $latestPosts = Post::published()
                              ->whereNotNull('title_en')->where('title_en', '!=', '')
                              ->whereNotNull('content_en')->where('content_en', '!=', '')
                              ->orderBy('published_at', 'desc')
                              ->limit(6)
                              ->get();
        } else {
            $latestPosts = Post::published()
                              ->orderBy('published_at', 'desc')
                              ->limit(6)
                              ->get();
        }
        
        // Get active categories
        $categories = Category::where('is_active', true)
                             ->orderBy('sort_order')
                             ->get();

        $didYouKnowCategory = Category::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('name_ar', 'like', '%هل تعلم%')
                    ->orWhere('name_en', 'like', '%did you know%')
                    ->orWhere('slug', 'like', '%did-you-know%')
                    ->orWhere('slug', 'like', '%hal-taalem%');
            })
            ->first();

        $didYouKnowUrl = $didYouKnowCategory
            ? route('posts.category', [$currentLocale, $didYouKnowCategory->slug])
            : route('posts.index', [$currentLocale]);

        $homeDocumentLinks = DocumentSection::active()
            ->forHomepage()
            ->withCount(['documents as published_documents_count' => function ($query) {
                $query->where('is_published', true);
            }])
            ->get()
            ->map(function (DocumentSection $section) use ($currentLocale) {
                return [
                    'title' => $section->home_label ?: $section->name,
                    'icon' => $section->home_icon ?: 'fa-folder',
                    'url' => route('frontend.documents.section', [$currentLocale, $section->slug]),
                    'count' => (int) ($section->published_documents_count ?? 0),
                ];
            });

        if (!$lockTemplate && $currentLocale === 'en') {
            $viewTemplate = 'frontend.home-en';
        }

        return view($viewTemplate, compact('sliderPosts', 'releasePosts', 'otherReportsPosts', 'latestPosts', 'categories', 'didYouKnowUrl', 'homeDocumentLinks'));
    }
    
    /**
     * Display the about page
     */
    public function about($locale = null)
    {
        return view('frontend.about');
    }
    
    /**
     * Display the contact page
     */
    public function contact($locale = null)
    {
        // توليد كابتشا حسابية بسيطة وحفظ الإجابة في السيشن
        $a = random_int(1, 9);
        $b = random_int(1, 9);
        session(['contact_captcha_answer' => $a + $b]);
        $captchaQuestion = "$a + $b";

        return view('frontend.contact', compact('captchaQuestion'));
    }
    
    /**
     * Display the privacy policy page
     */
    public function privacy($locale = null)
    {
        return view('frontend.privacy');
    }
    
    /**
     * Display the terms of service page
     */
    public function terms($locale = null)
    {
        return view('frontend.terms');
    }
    
    /**
     * Display the sitemap page
     */
    public function sitemap($locale = null)
    {
        return view('frontend.sitemap');
    }
    
    /**
     * Handle newsletter subscription
     */
    public function newsletterSubscribe(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        try {
            // تحقق إن كان البريد مشترك مسبقاً
            if (NewsletterSubscription::where('email', $validated['email'])->exists()) {
                $message = __('messages.newsletter_already_subscribed');
                if ($request->expectsJson()) {
                    return response()->json(['success' => true, 'message' => $message]);
                }
                return back()->with('success', $message);
            }

            NewsletterSubscription::create([
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'],
            ]);

            $message = __('messages.newsletter_success');
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            return back()->with('success', $message);
        } catch (\Throwable $e) {
            $message = __('messages.newsletter_error');
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->with('error', $message);
        }
    }
}
