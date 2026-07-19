<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\PostController;
use App\Http\Controllers\Frontend\DocumentController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ContactController;

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DocumentSectionController;
use App\Http\Controllers\Admin\DocumentCustomFieldController;
use App\Http\Controllers\Admin\DocumentController as AdminDocumentController;
use App\Http\Controllers\Admin\NashraController as AdminNashraController;
use App\Http\Controllers\Frontend\NashraController as FrontendNashraController;
use App\Http\Controllers\AuthController;



// Frontend Routes with Language Prefix
Route::group([
    'prefix' => '{locale?}',
    'where' => ['locale' => 'ar|en'],
    // Ensure model binding middleware runs for this group
    'middleware' => [\Illuminate\Routing\Middleware\SubstituteBindings::class, 'setlocale'],
], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/home1', [HomeController::class, 'home1'])->name('home1');
    Route::get('/home2', [HomeController::class, 'home2'])->name('home2');
    Route::get('/home3', [HomeController::class, 'home3'])->name('home3');

    // Posts Routes
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/search', [PostController::class, 'search'])->name('posts.search');
    Route::get('/posts/keywords', [\App\Http\Controllers\Frontend\KeywordController::class, 'postsIndex'])->name('posts.keywords.index');
    Route::get('/posts/keywords/{keyword}', [\App\Http\Controllers\Frontend\KeywordController::class, 'postsShow'])->name('posts.keywords.show');
    Route::get('/posts/{category}', [PostController::class, 'category'])->name('posts.category');
    // Redirect old slug-based URLs to new ID-based URLs
    Route::get('/posts/{category}/{slug}', function($locale, $category, $slug) {
        // Try to find post by slug and redirect to new format
        $post = \App\Models\Post::where('slug', $slug)->first();
        if ($post && $post->category) {
            return redirect()->route('content.show', [$locale, $post->category->slug, $post->id], 301);
        }
        abort(404);
    })->where('slug', '[^0-9][^/]*'); // Only match non-numeric slugs
    // Download specific attached file (placed before slug-based download to avoid route conflicts)
    Route::get('/posts/files/{file}/download', [PostController::class, 'downloadAttachment'])->name('posts.files.download');
    // Download post main file by category and slug
    Route::get('/posts/{category}/{slug}/download', [PostController::class, 'downloadFile'])->name('posts.download');
    
    // Documents Routes
    Route::prefix('documents')->name('frontend.documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('/search', [DocumentController::class, 'search'])->name('search');
        Route::post('/search', [DocumentController::class, 'search'])->name('search.post');
        Route::get('/keywords', [\App\Http\Controllers\Frontend\KeywordController::class, 'documentsIndex'])->name('keywords.index');
        Route::get('/keywords/{keyword}', [\App\Http\Controllers\Frontend\KeywordController::class, 'documentsShow'])->name('keywords.show');
    
        Route::get('/custom-fields', [DocumentController::class, 'getCustomFields'])->name('custom-fields');
        Route::get('/files/{file}/download', [DocumentController::class, 'downloadAttachment'])->name('files.download');
        Route::get('/section/{section}', [DocumentController::class, 'section'])->name('section');
        Route::post('/section/{section}', [DocumentController::class, 'section'])->name('section.post');
        Route::post('/section/{section}/filter-counts', [DocumentController::class, 'getFilterCounts'])->name('filter-counts');
        Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
    });

    // Nashras Routes
    Route::prefix('nashras')->name('frontend.nashras.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Frontend\NashraController::class, 'index'])->name('index');
        Route::get('/search', [\App\Http\Controllers\Frontend\NashraController::class, 'search'])->name('search');
        Route::get('/latest', [\App\Http\Controllers\Frontend\NashraController::class, 'latest'])->name('latest');
        Route::get('/api/sheets/{nashra}', [\App\Http\Controllers\Frontend\NashraController::class, 'getSheetData'])->name('api.sheets');
        Route::get('/{nashra}/get-sheet-data', [\App\Http\Controllers\Frontend\NashraController::class, 'getSheetData'])->name('get-sheet-data');
        Route::get('/{nashra}', [\App\Http\Controllers\Frontend\NashraController::class, 'show'])->name('show');
        Route::get('/{nashra}/download', [\App\Http\Controllers\Frontend\NashraController::class, 'downloadPdf'])->name('download');
        Route::get('/{nashra}/download-pdf', [\App\Http\Controllers\Frontend\NashraController::class, 'downloadPdf'])->name('download-pdf');
    });

    // Podcasts Routes
    Route::prefix('podcasts')->name('frontend.podcasts.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Frontend\PodcastController::class, 'index'])->name('index');
        Route::get('/{podcast}/stream', [\App\Http\Controllers\Frontend\PodcastController::class, 'stream'])->name('stream');
        Route::get('/{podcast}/tracks/{track}/stream', [\App\Http\Controllers\Frontend\PodcastController::class, 'streamTrack'])->name('tracks.stream');
        Route::get('/{podcast}', [\App\Http\Controllers\Frontend\PodcastController::class, 'show'])->name('show');
    });

    // Unified content route: /{sectionSlug}/{id}
    // Put after specific routes to avoid conflicts; constrain id to numeric and sectionSlug to allow any non-slash chars (supports Arabic slugs)
    Route::get('/{sectionSlug}/{id}', [DocumentController::class, 'unifiedShow'])
        ->where(['id' => '[0-9]+', 'sectionSlug' => '[^/]+' ])
        ->name('content.show');
    
    // Newsletter
    Route::post('/newsletter/subscribe', [HomeController::class, 'newsletterSubscribe'])->name('newsletter.subscribe');
    
    // Static Pages
    Route::get('/about', [HomeController::class, 'about'])->name('about');
    Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
    // Added: contact form submission
    Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
    Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
    Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
    Route::get('/sitemap', [HomeController::class, 'sitemap'])->name('sitemap');
});

// Fallback routes without language prefix (redirect to Arabic)
Route::get('/', function() {
    return redirect('/ar');
});
Route::get('/posts', function() {
    return redirect('/ar/posts');
});
Route::get('/posts/{category}/{slug}', function($category, $slug) {
    // Try to find post by slug and redirect to new format
    $post = \App\Models\Post::where('slug', $slug)->first();
    if ($post && $post->category) {
        return redirect()->route('content.show', ['ar', $post->category->slug, $post->id], 301);
    }
    return redirect('/ar/posts/' . $category);
});
Route::get('/documents', function() {
    return redirect('/ar/documents');
});

Route::get('/documents/{slug}', function($slug) {
    return redirect('/ar/documents/' . $slug);
});
Route::get('/contact', function() {
    return redirect('/ar/contact');
});
Route::get('/about', function() {
    return redirect('/ar/about');
});

// Authentication Routes
$adminPath = trim((string) config('admin.path', 'panel'), '/');

Route::get("/{$adminPath}/login", [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post("/{$adminPath}/login", [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post("/{$adminPath}/logout", [AuthController::class, 'logout'])->name('admin.logout');

// Admin Routes (Protected) — staff only (admin | editor)
Route::prefix($adminPath)->name('admin.')->middleware(['auth', 'role:admin,editor'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Profile Management
    Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
    Route::put('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');
    
    // Settings Management — admins only
    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::get('/settings/index', [AdminController::class, 'settings'])->name('settings.index');
        Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    });
    
    // Categories Management
    Route::resource('categories', CategoryController::class);
    Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
    
    // Posts Management
    Route::resource('posts', AdminPostController::class);
    
    // Document Sections Management
    Route::resource('document-sections', DocumentSectionController::class);
    Route::post('document-sections/reorder', [DocumentSectionController::class, 'reorder'])->name('document-sections.reorder');
    Route::post('document-sections/{section}/toggle-status', [DocumentSectionController::class, 'toggleStatus'])->name('document-sections.toggle-status');
    Route::get('document-sections/{section}/custom-fields', [DocumentCustomFieldController::class, 'indexBySection'])->name('document-sections.custom-fields.index');
    
    // Document Custom Fields Management
    Route::resource('document-custom-fields', DocumentCustomFieldController::class);
    Route::get('document-custom-fields/section/{section}', [DocumentCustomFieldController::class, 'indexBySection'])->name('document-custom-fields.by-section');
    Route::post('document-custom-fields/{documentCustomField}/toggle-status', [DocumentCustomFieldController::class, 'toggleStatus'])->name('document-custom-fields.toggle-status');
    Route::post('document-custom-fields/reorder', [DocumentCustomFieldController::class, 'reorder'])->name('document-custom-fields.reorder');
    
    // Documents Management
    Route::get('documents/custom-fields', [AdminDocumentController::class, 'getCustomFields'])->name('documents.custom-fields');
    Route::put('documents/pinned-keywords', [AdminDocumentController::class, 'updatePinnedKeywords'])->name('documents.pinned-keywords.update');
    Route::resource('documents', AdminDocumentController::class);
    Route::post('documents/bulk-publish', [AdminDocumentController::class, 'bulkPublish'])->name('documents.bulk-publish');
    Route::post('documents/bulk-unpublish', [AdminDocumentController::class, 'bulkUnpublish'])->name('documents.bulk-unpublish');
    Route::post('documents/bulk-feature', [AdminDocumentController::class, 'bulkFeature'])->name('documents.bulk-feature');
    Route::post('documents/bulk-delete', [AdminDocumentController::class, 'bulkDelete'])->name('documents.bulk-delete');
    Route::post('documents/{document}/toggle-status', [AdminDocumentController::class, 'toggleStatus'])->name('documents.toggle-status');
    Route::post('documents/{document}/toggle-featured', [AdminDocumentController::class, 'toggleFeatured'])->name('documents.toggle-featured');
    Route::delete('documents/files/{file}', [AdminDocumentController::class, 'deleteFile'])->name('documents.files.delete');
    Route::get('documents/files/{file}/download', [AdminDocumentController::class, 'downloadFile'])->name('documents.files.download');

    // Nashras Management
    Route::post('nashras/{nashra}/refresh-sheet-cache', [\App\Http\Controllers\Admin\NashraController::class, 'refreshSheetCache'])->name('nashras.refresh-sheet-cache');
    Route::resource('nashras', \App\Http\Controllers\Admin\NashraController::class);
    Route::get('nashras/{nashra}/sheets', [\App\Http\Controllers\Admin\NashraController::class, 'fetchSheets'])->name('nashras.sheets');
    Route::post('nashras/fetch-sheets', [\App\Http\Controllers\Admin\NashraController::class, 'fetchSheets'])->name('nashras.fetch-sheets');
    Route::post('nashras/{nashra}/toggle-status', [\App\Http\Controllers\Admin\NashraController::class, 'toggleStatus'])->name('nashras.toggle-status');

    // Podcasts Management
    Route::get('podcasts/{podcast}/stream', [\App\Http\Controllers\Admin\PodcastController::class, 'stream'])->name('podcasts.stream');
    Route::get('podcasts/{podcast}/tracks/{track}/stream', [\App\Http\Controllers\Admin\PodcastController::class, 'streamTrack'])->name('podcasts.tracks.stream');
    Route::resource('podcasts', \App\Http\Controllers\Admin\PodcastController::class);

    // Keywords autocomplete
    Route::get('keywords/suggest', [\App\Http\Controllers\Admin\KeywordController::class, 'suggest'])->name('keywords.suggest');
    
    // Inquiries (Contact) Management
    Route::prefix('inquiries')->name('inquiries.')->group(function () {
        $controller = \App\Http\Controllers\Admin\InquiryController::class;
        Route::get('/', [$controller, 'index'])->name('index');
        Route::get('/{inquiry}', [$controller, 'show'])->name('show');
        Route::delete('/{inquiry}', [$controller, 'destroy'])->name('destroy');
        Route::post('/{inquiry}/mark-read', [$controller, 'markRead'])->name('mark-read');
        Route::post('/{inquiry}/mark-unread', [$controller, 'markUnread'])->name('mark-unread');
    });
     
      
    
    // Users Management — admins only
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    });
    
    // Comments Management
    Route::resource('comments', \App\Http\Controllers\Admin\CommentController::class);
    
    // Media Management
    Route::resource('media', \App\Http\Controllers\Admin\MediaController::class);
    
    // Newsletter Subscriptions Management
    Route::resource('newsletter-subscriptions', \App\Http\Controllers\Admin\NewsletterSubscriptionController::class)
        ->only(['index', 'destroy']);
    // إضافة: تصدير CSV وحذف جماعي لاشتراكات النشرة
    Route::get('newsletter-subscriptions/export', [\App\Http\Controllers\Admin\NewsletterSubscriptionController::class, 'export'])->name('newsletter-subscriptions.export');
    Route::post('newsletter-subscriptions/bulk-delete', [\App\Http\Controllers\Admin\NewsletterSubscriptionController::class, 'bulkDelete'])->name('newsletter-subscriptions.bulk-delete');
});
Route::redirect("/{$adminPath}", "/{$adminPath}/dashboard");

// Language switching
use App\Http\Controllers\LanguageController;
Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// Fallback routes to redirect to Arabic version
Route::get('/posts/{category}', function ($category) {
    return redirect('/ar/posts/' . $category);
});
