@extends('frontend.layouts.app')

@section('title', ($siteName ?? config('app.name', 'MK Snow')) . ' - Home')

@section('content')
<!-- English Homepage Layout -->

<!-- Slider Posts Section - 3 images side by side -->
 <br>
 <br>
<div class="blog-section">
    <div class="container">
        @if(isset($sliderPosts) && $sliderPosts->count() > 0)
            <div class="row g-3 mb-4">
                @foreach($sliderPosts->take(3) as $post)
                    <div class="col-md-4">
                        <a href="{{ route('content.show', [app()->getLocale(), $post->category->name_en ?: $post->category->slug, $post->id]) }}" class="text-decoration-none">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="position-relative overflow-hidden">
                                    <img src="{{ $post->background_image ? asset('storage/' . $post->featured_image) : ($post->background_image ? asset('storage/' . $post->featured_image) : 'https://via.placeholder.com/400x250?text=No+Image') }}" 
                                         class="card-img-top" 
                                         style="height: 250px; object-fit: cover;" 
                                         alt="{{ $post->title }}">
                                    <div class="top-badge">
                                        {{ optional($post->category)->name_en ?? 'Uncategorized' }}
                                    </div>
                                    <div class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-75 text-white p-3">
                                        
                                        <h6 class="mb-0 text-white">{{ $post->title_en ?: $post->title }}</h6>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<br>

<!-- Blog Section with Navigation Links -->
<div class="blog-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3">
                <a href="#" class="text-decoration-none">
                    <div class="card h-100 text-center border-0 shadow-sm hover-card">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <i class="fas fa-file-alt fa-4x text-primary mb-3"></i>
                            <h5 class="card-title text-dark">Reports</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="#" class="text-decoration-none">
                    <div class="card h-100 text-center border-0 shadow-sm hover-card">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <i class="fas fa-newspaper fa-4x text-primary mb-3"></i>
                            <h5 class="card-title text-dark">Press Releases</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('contact', app()->getLocale()) }}" class="text-decoration-none">
                    <div class="card h-100 text-center border-0 shadow-sm hover-card">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <i class="fas fa-envelope fa-4x text-primary mb-3"></i>
                            <h5 class="card-title text-dark">Contact Us</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('about', app()->getLocale()) }}" class="text-decoration-none">
                    <div class="card h-100 text-center border-0 shadow-sm hover-card">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <i class="fas fa-info-circle fa-4x text-primary mb-3"></i>
                            <h5 class="card-title text-dark">Who We Are</h5>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<br>

<!-- Other Reports Section - 4 smaller images -->
<div class="blog-section">
    <div class="container">
        <h2 class="mb-4">
            Other Publications
            <div></div>
        </h2>
        @if(isset($releasePosts) && $releasePosts->count() > 0)
            <div class="row g-3">
                @foreach($releasePosts->take(4) as $post)
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('content.show', [app()->getLocale(), $post->category->name_en ?: $post->category->slug, $post->id]) }}" class="text-decoration-none">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="position-relative overflow-hidden">
                                    @if($post->featured_image)
                                        <img src="{{ asset('storage/' . $post->featured_image) }}" 
                                             class="card-img-top" 
                                             style="height: 180px; object-fit: cover;" 
                                             alt="{{ $post->title }}">
                                    @endif
                                    <div class="top-badge">
                                        {{ optional($post->category)->name_en ?? 'Uncategorized' }}
                                    </div>
                                    <div class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-75 text-white p-2">
                                        <h6 class="mb-0 text-white small">{{ $post->title_en ?: $post->title }}</h6>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@endsection

@push('styles')
<style>
.position-relative { position: relative; }
.top-badge {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: #ffffff;
    color: #333;
    border: 1px solid rgba(0,0,0,0.1);
    padding: 0.25rem 0.4rem;
    font-size: 0.75rem;
    line-height: 1;
    z-index: 3;
}
.hover-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.card-img-top {
    transition: transform 0.3s ease;
}

.card:hover .card-img-top {
    transform: scale(1.05);
}
</style>
@endpush