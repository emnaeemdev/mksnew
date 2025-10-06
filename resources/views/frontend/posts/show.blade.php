@extends('frontend.layouts.app')

@section('title', $post->title . 'MKSEGYPT')
@section('description', Str::limit(strip_tags($post->content), 160))

@push('meta')
    <!-- Meta Tags -->
    <meta name="keywords" content="{{ optional($post->category)->name ? optional($post->category)->name . ', MK Snow' : 'MK Snow' }}">
    <meta name="author" content="MK Snow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $post->title }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($post->content), 160) }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url()->current() }}">
    
    @if($post->featured_image)
    <meta property="og:image" content="{{ asset('storage/' . $post->featured_image) }}">
    @endif
@endpush

@section('content')
    
    <!-- Page Header -->



                      <section class="page-header">
    <div class="container {{ app()->getLocale() == 'ar' ? 'text-end' : 'text-start' }}">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb {{ app()->getLocale() == 'ar' ?  : '' }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}">
                        @if(app()->getLocale() == 'ar')
                            {{ 'الرئيسية' }}
                            <i class="fas fa-home ms-1"></i>
                        @else
                            <i class="fas fa-home me-1"></i>
                            {{ 'Home' }}
                        @endif
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('posts.index', [app()->getLocale(), 'category' => $post->category->slug]) }}">
                        {{ $post->category->name }}
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    {{ Str::limit($post->title, 90) }}
                </li>
            </ol>
        </nav>
    </div>
</section>
    <!-- Post Hero Section (only if background image exists) -->
    @if($post->background_image)
    <div class="post-hero" style="background-image: url('{{ asset('storage/' . $post->background_image) }}');">
        <div class="post-hero-overlay"></div>
        <div class="container">
            <div class="post-hero-content">
                <h1 class="post-hero-title">{{ $post->title }}</h1>
                <div class="post-hero-meta">
                    <a href="{{ route('posts.index', [app()->getLocale(), 'category' => $post->category->slug]) }}" class="post-category me-3">
                        <i class="fas fa-tag me-1"></i>
                        {{ $post->category->name }}
                    </a>
                    <span class="me-3">
                        <i class="fas fa-calendar me-1"></i>
                        {{ $post->published_at ? $post->published_at->format('Y-m-d') : $post->created_at->format('Y-m-d') }}
                    </span>
                    <span>
                        <i class="fas fa-eye me-1"></i>
                        {{ $post->views_count }} {{ app()->getLocale() == 'ar' ? 'مشاهدة' : 'views' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                
                <!-- Post Content -->
                <article class="post-container">
                    
                    <!-- Post Title with Border (when no background image) -->
                    @if(!$post->background_image)
                    <div class="post-title-with-border">
                        <h1 class="post-title">{{ $post->title }}</h1>
                        <div class="post-meta">
                            <div class="post-meta-item">
                                <a href="{{ route('posts.index', [app()->getLocale(), 'category' => $post->category->slug]) }}" class="post-category">
                                    <i class="fas fa-tag me-1"></i>
                                    {{ $post->category->name }}
                                </a>
                            </div>
                            <div class="post-meta-item">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $post->published_at ? $post->published_at->format('Y-m-d') : $post->created_at->format('Y-m-d') }}
                            </div>
                            <div class="post-meta-item">
                                <i class="fas fa-eye me-1"></i>
                                {{ $post->views_count }} {{ app()->getLocale() == 'ar' ? 'مشاهدة' : 'views' }}
                            </div>
                        </div>
                    </div>
                    @endif

                    
                    <!-- Post Content -->
                        <div class="post-content">
                            {!! $post->content !!}
                        </div>

                    
                    <!-- Download Section -->
                    @php
                        $currentLocale = app()->getLocale();
                        $languageFiles = $post->files->where('language', $currentLocale);
                    @endphp
                    @if($languageFiles && $languageFiles->count() > 0)
                    <div class="download-section">
                        <div class="download-icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <h4 class="mb-3">{{ $currentLocale == 'ar' ? 'الملفات المرفقة' : 'Attached Files' }}</h4>

                        <div class="files-list">
                            @foreach($languageFiles as $file)
                            <div class="file-item mb-3 p-3 border rounded">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-1">{{ $file->display_name ?: $file->original_name }}</h6>
                                        <small class="text-muted">
                                            {{ $file->original_name }} • {{ $file->formattedSize }}
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <a href="{{ route('posts.files.download', ['locale' => app()->getLocale(), 'file' => $file->id]) }}" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-download me-1"></i>
                                            {{ $currentLocale == 'ar' ? 'تحميل' : 'Download' }}
                                        </a>
                                        <div class="mt-2 text-muted small">
                                            {{ $currentLocale == 'ar' ? 'عدد التحميلات' : 'Downloads' }}: {{ $file->download_count ?? 0 }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <!-- Post Actions -->
                    <div class="post-actions">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3">
                                    <i class="fas fa-share-alt me-2"></i>
                                    {{ app()->getLocale() == 'ar' ? 'شارك هذا الموضوع' : 'Share this topic' }}
                                </h6>
                                <div class="share-buttons">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" 
                                       target="_blank" class="share-btn share-facebook">
                                        <i class="fab fa-facebook-f me-1"></i>
                                        Facebook
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($post->title) }}" 
                                       target="_blank" class="share-btn share-twitter">
                                        <i class="fab fa-twitter me-1"></i>
                                        Twitter
                                    </a>
                                    <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . url()->current()) }}" 
                                       target="_blank" class="share-btn share-whatsapp">
                                        <i class="fab fa-whatsapp me-1"></i>
                                        WhatsApp
                                    </a>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="mb-3">
                            
                                   
                                </h6>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('posts.index', [app()->getLocale(), 'category' => $post->category->slug]) }}"
                   class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-list me-1"></i>
                                        {{ app()->getLocale() == 'ar' ? ($post->category ? 'المزيد من ' . $post->category->name : 'كل المقالات') : ($post->category ? 'More from ' . $post->category->name : 'All posts') }}
                                    </a>
                                    <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-home me-1"></i>
                                        {{ app()->getLocale() == 'ar' ? 'الرئيسية' : 'Home' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
            
            <div class="col-lg-4">
                <div class="sticky-top" style="top: 100px;">
                    <!-- Other Reports -->
                    @if($otherReportsPosts->count() > 0)
                    <div class="related-posts">
                    <h4 class="mb-4">
                        <i class="fas fa-file-alt me-2"></i>
                        {{ app()->getLocale() == 'ar' ? 'التقارير الأخرى' : 'Other Reports' }}
                    </h4>
                    
                    @foreach($otherReportsPosts as $otherPost)
                    <a href="{{ ($otherPost->category && $otherPost->category->name) ? route('content.show', [app()->getLocale(), $otherPost->category->slug, $otherPost->id]) : route('posts.index', [app()->getLocale()]) }}" 
                       class="related-post-card">
                        <img class="related-thumb" src="{{ $otherPost->featured_image ? asset('storage/' . $otherPost->featured_image) : 'https://via.placeholder.com/120x80?text=No+Image' }}" alt="{{ $otherPost->title }}">
                        <div class="related-post-body">
                            <div class="related-post-title">{{ Str::limit($otherPost->title, 60) }}</div>
                            <div class="related-post-meta">
                                <i class="fas fa-tag me-1"></i>
                                {{ $otherPost->category->name }}
                                <span class="float-end">
                                    <i class="fas fa-eye me-1"></i>
                                    {{ $otherPost->views_count }}
                                </span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
                    @endif
                    
                    <!-- Related Posts -->
                    @if($relatedPosts->count() > 0)
                    <div class="related-posts mt-4">
                    <h4 class="mb-4">
                        <i class="fas fa-newspaper me-2"></i>
                        {{ app()->getLocale() == 'ar' ? 'مواضيع ذات صلة' : 'Related Topics' }}
                    </h4>
                    
                    @foreach($relatedPosts as $relatedPost)
                    <a href="{{ ($relatedPost->category && $relatedPost->category->name) ? route('content.show', [app()->getLocale(), $relatedPost->category->slug, $relatedPost->id]) : route('posts.index', [app()->getLocale()]) }}" class="related-post-card">
                        <img class="related-thumb" src="{{ $relatedPost->featured_image ? asset('storage/' . $relatedPost->featured_image) : 'https://via.placeholder.com/120x80?text=No+Image' }}" alt="{{ $relatedPost->title }}">
                        <div class="related-post-body">
                            <div class="related-post-title">{{ Str::limit($relatedPost->title, 60) }}</div>
                            <div class="related-post-meta">
                                <i class="fas fa-tag me-1"></i>
                                {{ $relatedPost->category->name }}
                                <span class="float-end">
                                    <i class="fas fa-eye me-1"></i>
                                    {{ $relatedPost->views_count }}
                                </span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                    
                    <div class="text-center mt-3">
                        <a href="{{ route('posts.index', [app()->getLocale(), 'category' => $post->category->slug]) }}" 
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-arrow-right me-1"></i>
                            {{ app()->getLocale() == 'ar' ? 'عرض المزيد' : 'View More' }}
                        </a>
                    </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    

    
    <!-- Bootstrap JS -->

    
    <script>
        // Track page view time for analytics
        let startTime = Date.now();
        
        window.addEventListener('beforeunload', function() {
            let timeSpent = Math.round((Date.now() - startTime) / 1000);
            
            // Send analytics data (you can implement this based on your needs)
            if (timeSpent > 5) { // Only track if user spent more than 5 seconds
                console.log('Time spent on page:', timeSpent, 'seconds');
            }
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
@endsection