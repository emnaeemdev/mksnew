<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\NewsletterSubscription;

class HomeController extends Controller
{
    /**
     * Display the homepage
     */
    public function index($locale = null)
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
                               ->limit(8)
                               ->get();
        } else {
            $releasePosts = Post::published()
                               ->forReleasesEn()
                               ->orderBy('sort_order')
                               ->orderBy('published_at', 'desc')
                               ->limit(8)
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
        
        // Use different template based on language
        $viewTemplate = $currentLocale === 'en' ? 'frontend.home-en' : 'frontend.home';
        
        return view($viewTemplate, compact('sliderPosts', 'releasePosts', 'otherReportsPosts', 'latestPosts', 'categories'));
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
        return view('frontend.contact');
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
            'email' => 'required|email:rfc,dns|max:255',
        ]);

        try {
            // تحقق إن كان البريد مشترك مسبقاً
            if (NewsletterSubscription::where('email', $validated['email'])->exists()) {
                return back()->with('success', __('messages.newsletter_already_subscribed'));
            }

            NewsletterSubscription::create([
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'],
            ]);

            return back()->with('success', __('messages.newsletter_success'));
        } catch (\Throwable $e) {
            return back()->with('error', __('messages.newsletter_error'));
        }
    }
}
