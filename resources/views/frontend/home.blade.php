@extends('frontend.layouts.app')

@section('title', app()->isLocale('ar') ? (($siteName ?? config('app.name', 'MK Snow')) . ' - الصفحة الرئيسية') : (($siteName ?? config('app.name', 'MK Snow')) . ' - Home'))


@section('content')
<div class="blog-section" >
    <div class="layout-container">
        <div class="main-content">
            @if(isset($sliderPosts) && $sliderPosts->count() > 0)
                @php $mainPost = $sliderPosts->first(); @endphp

                <!-- الصورة الرئيسية -->
                <a href="{{ route('content.show', [app()->getLocale(), $mainPost->category->name_en ?: $mainPost->category->slug, $mainPost->id]) }}">
                     <div class="main-image">
                         <div class="main-blog-title">
                             <img class="main-img" src="{{ $mainPost->background_image ? asset('storage/' . $mainPost->background_image) : ($mainPost->featured_image ? asset('storage/' . $mainPost->featured_image) : 'https://via.placeholder.com/800x400?text=No+Image') }}" alt="صورة رئيسية">
                             <div class="category-badge">
                                 {{ app()->isLocale('ar') ? ($mainPost->category->name_ar ?? $mainPost->category->name ?? 'غير مصنف') : ($mainPost->category->name_en ?? $mainPost->category->name ?? 'Uncategorized') }}
                             </div>
                             <div class="overlay-title">
                                <div>  {{ $mainPost->title }}</div>
                             </div>
                         </div>
                     </div>
                 </a>

                <!-- الصور الثلاثة الفرعية -->
                 <div class="small-images-container">
                     @foreach($sliderPosts->skip(1)->take(3) as $post)
                         <div class="small-image">
                            <a href="{{ route('content.show', [app()->getLocale(), $post->category->name_en ?: $post->category->slug, $post->id]) }}" class="text-dark text-decoration-none">
                                 <div class="main-blog-title">
                                     <img class="small-img" src="{{ $post->featured_image ? asset('storage/' . $post->featured_image) : ($post->background_image ? asset('storage/' . $post->background_image) : 'https://via.placeholder.com/400x200?text=No+Image') }}" alt="صورة فرعية">
                                     <div class="category-badge">
                                         {{ app()->isLocale('ar') ? ($post->category->name_ar ?? $post->category->name ?? 'غير مصنف') : ($post->category->name_en ?? $post->category->name ?? 'Uncategorized') }}
                                     </div>
                                     <div class="overlay-title">
                                         <div>{{ $post->title }}</div>
                                     </div>
                                 </div>
                             </a>
                         </div>
                     @endforeach
                 </div>
            @endif
        </div>

        <!-- العمود الأيسر -->
        <div class="sidebar text-white">
            <a href="#" class="text-white text-decoration-none">
                <div class="sidebar-item">
                    <i class="fas fa-window-restore fa-4x mb-4"></i>
                    <h6 class="law-title">مجلة نوافذ</h6>
                </div>
            </a>

            <a href="{{ route('frontend.podcasts.index', [app()->getLocale()]) }}" class="text-white text-decoration-none">
                <div class="sidebar-item">
                    <i class="fas fa-podcast fa-4x mb-4"></i>
                    <h6 class="law-title">بودكاست محكمة</h6>
                </div>
            </a>

            <a href="{{ route('frontend.nashras.index', [app()->getLocale()]) }}" class="text-white text-decoration-none">
                <div class="sidebar-item">
                    <i class="fas fa-chart-bar fa-4x mb-4"></i>
                    <h6 class="law-title"> نشرة الذاكرة القانونية</h6>
                </div>
            </a>

            <a href="#" class="text-white text-decoration-none">
                <div class="sidebar-item">
                    <i class="fas fa-lightbulb fa-4x mb-4"></i>
                    <h6 class="law-title">هل تعلم</h6>
                </div>
            </a>
        </div>

    </div>

    <br>
</div>

<div class="container">
    <hr style="height: 2px; background-color: #2b4596; border: none;">
</div>



<div class="blog-section">
    <div class="container">
        <h2>
            إصدرات أخرى
            <div></div>
        </h2>
        @if(isset($releasePosts) && $releasePosts->count() > 0)
            <div class="row">
                @foreach($releasePosts as $post)
                    <div class="col-md-3 mb-4">
                    <a href="{{ route('content.show', [app()->getLocale(), $post->category->name_en ?: $post->category->slug, $post->id]) }}">
                             <div class="main-blog-title-t">
                                 <div class="custom-blog">
                                     <div class="custom-img-container mb-3 position-relative">
                                         @if($post->featured_image)
                                             <img src="{{ asset('storage/' . $post->featured_image) }}" class="img-fluid img-thumbnail effect-img" />
                                         @endif
                                         <div class="category-badge">
                                             {{ app()->isLocale('ar') ? (optional($post->category)->name_ar ?? 'غير مصنف') : (optional($post->category)->name_en ?? 'Uncategorized') }}
                                         </div>
                                     </div>
                                     <div>{{ $post->title }}</div>
                                 </div>
                             </div>
                         </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<div class="container">

    <hr style="height: 2px; background-color: #2b4596; border: none;">

</div>

@if(app()->isLocale('ar'))
<div class="fsec-section" >
    <br>
    <br>
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-5">
                <a href="#" class="card-link text-decoration-none">
                    <div class="cardhome text-center p-4">
                        <i class="fas fa-balance-scale fa-3x mb-3"></i>
                        <h6 class="card-title">قوانين</h6>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-5">
                <a href="#" class="card-link text-decoration-none">
                    <div class="cardhome text-center p-4">
                        <i class="fas fa-clipboard-check fa-3x mb-3"></i>
                        <h6 class="card-title">قرارات</h6>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-5">
                <a href="#" class="card-link text-decoration-none">
                    <div class="cardhome text-center p-4">
                        <i class="fas fa-gavel fa-3x mb-3"></i>
                        <h6 class="card-title">المحكمة الدستورية</h6>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-5">
                <a href="#" class="card-link text-decoration-none">
                    <div class="cardhome text-center p-4">
                        <i class="fas fa-building fa-3x mb-3"></i>
                        <h6 class="card-title">محكمة النقض</h6>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-5">
                <a href="#" class="card-link text-decoration-none">
                    <div class="cardhome text-center p-4">
                        <i class="fas fa-institution fa-3x mb-3"></i>
                        <h6 class="card-title">المحكمة الادارية العليا</h6>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <br>
    <br>
</div>
@endif
@endsection

@push('styles')
<style>
.main-blog-title { position: relative; }
.category-badge {
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
</style>
@endpush