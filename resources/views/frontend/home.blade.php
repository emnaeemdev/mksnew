@extends('frontend.layouts.app')

@section('title', app()->isLocale('ar') ? (($siteName ?? config('app.name', 'MK Snow')) . ' - الصفحة الرئيسية') : (($siteName ?? config('app.name', 'MK Snow')) . ' - Home'))


@section('content')
<div class="blog-section" style="box-shadow: 0 1px 6px #e9640a73;">
    <div class="layout-container">
        <div class="main-content">
            @if(isset($sliderPosts) && $sliderPosts->count() > 0)
                @php $mainPost = $sliderPosts->first(); @endphp

                <!-- الصورة الرئيسية -->
                <a href="{{ route('content.show', [app()->getLocale(), $mainPost->category->name_en ?: $mainPost->category->slug, $mainPost->id]) }}">
                     <div class="main-image">
                         <div class="main-blog-title">
                             <img class="main-img" src="{{ $mainPost->background_image ? asset('storage/' . $mainPost->background_image) : ($mainPost->featured_image ? asset('storage/' . $mainPost->featured_image) : 'https://via.placeholder.com/800x400?text=No+Image') }}" alt="صورة رئيسية">
                             <div class="overlay-title">
                                <div><span class="btn btn-light btn-sm">{{ optional($mainPost->category)->name_en ?? (app()->isLocale('ar') ? 'بدون تصنيف' : 'Uncategorized') }}</span> | {{ $mainPost->title }}</div>
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
                    <h6 class="law-title">النشرات الإحصائية</h6>
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

<br>

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
                                     <div class="custom-img-container mb-3">
                                         @if($post->featured_image)
                                             <img src="{{ asset('storage/' . $post->featured_image) }}" class="img-fluid img-thumbnail effect-img" />
                                         @endif
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

@if(app()->isLocale('ar'))
<div class="fsec-section" style="box-shadow: 0 1px 6px #e9640a73;">
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