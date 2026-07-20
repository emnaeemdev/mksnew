@extends('frontend.layouts.app')

@section('title', (app()->isLocale('ar') ? 'تجربة تصميم 1 — معرض الأغلفة' : 'Home concept 1') . ' | ' . ($siteName ?? config('app.name')))
@section('body_class', 'concept-home1')

@section('content')
@php
    $locale = app()->getLocale();
    $isAr = app()->isLocale('ar');
    $slider = ($sliderPosts ?? collect())->values();
    $mainPost = $slider->get(0);
    $sec = $slider->slice(1, 3)->values();
    $brand = $siteName ?? 'الذاكرة والمعرفة للدراسات';

    $imgOf = function ($post, bool $bg = false) {
        if (!$post) return null;
        if ($bg && $post->background_image) return asset('storage/' . $post->background_image);
        if ($post->featured_image) return asset('storage/' . $post->featured_image);
        if ($post->background_image) return asset('storage/' . $post->background_image);
        return null;
    };
    $catOf = function ($post) use ($isAr) {
        if (!$post?->category) return '';
        return $isAr
            ? ($post->category->name_ar ?? $post->category->name ?? '')
            : ($post->category->name_en ?? $post->category->name ?? '');
    };
    $urlOf = function ($post) use ($locale) {
        return route('content.show', [$locale, $post->category->name_en ?: $post->category->slug, $post->id]);
    };
@endphp

<div class="g1" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
    <div class="g1-sky" aria-hidden="true"></div>

    <section class="g1-top">
        <div class="container">
            <div class="g1-intro g1-rise">
                <!-- <p class="g1-intro__eyebrow">{{ $isAr ? 'منصة معرفية' : 'Knowledge platform' }}</p> -->
                <h1 class="g1-intro__brand">{{ $brand }}</h1>
                <p class="g1-intro__lead">
                    <!-- {{ $isAr
                        ? 'ادخل من الغلاف — مواضيع مختارة، وثائق، وإصدارات في مسار بصري واحد.'
                        : 'Enter through the cover — selected stories, documents, and publications.' }} -->
                </p>
            </div>

            <div class="g1-channels-panel g1-rise" style="--d:.1s">
                <nav class="g1-channels" aria-label="{{ $isAr ? 'قنوات' : 'Channels' }}">
                    <a href="{{ url('/'.$locale.'/posts?category=nawafez') }}" class="g1-channels__link">
                        <span class="g1-channels__ico" aria-hidden="true"><i class="fas fa-window-restore"></i></span>
                        <span class="g1-channels__label">مجلة نوافذ</span>
                    </a>
                    <a href="{{ route('frontend.podcasts.index', [$locale]) }}" class="g1-channels__link">
                        <span class="g1-channels__ico" aria-hidden="true"><i class="fas fa-podcast"></i></span>
                        <span class="g1-channels__label">بودكاست محكمة</span>
                    </a>
                    <a href="{{ route('frontend.nashras.index', [$locale]) }}" class="g1-channels__link">
                        <span class="g1-channels__ico" aria-hidden="true"><i class="fas fa-newspaper"></i></span>
                        <span class="g1-channels__label">نشرة الذاكرة القانونية</span>
                    </a>
                    <a href="{{ $didYouKnowUrl ?? '#' }}" class="g1-channels__link">
                        <span class="g1-channels__ico" aria-hidden="true"><i class="fas fa-lightbulb"></i></span>
                        <span class="g1-channels__label">هل تعلم</span>
                    </a>
                </nav>
            </div>

            @if($mainPost)
                @php $secTwo = $sec->take(2); @endphp
                {{-- رئيسي يمين + فرعيان عموديان شمال — العنوان على الصورة --}}
                <div class="g1-mosaic g1-rise" style="--d:.18s">
                    <a class="g1-cover g1-cover--hero" href="{{ $urlOf($mainPost) }}">
                        <div class="g1-cover__frame">
                            @if($src = $imgOf($mainPost, true))
                                <img src="{{ $src }}" alt="{{ $mainPost->title }}">
                            @endif
                            <div class="g1-cover__caption">
                                <span>{{ $catOf($mainPost) }}</span>
                                <h2>{{ $mainPost->title }}</h2>
                            </div>
                        </div>
                    </a>

                    <div class="g1-side">
                        @foreach($secTwo as $i => $post)
                            <a class="g1-cover g1-cover--side" href="{{ $urlOf($post) }}">
                                <div class="g1-cover__frame">
                                    @if($src = $imgOf($post))
                                        <img src="{{ $src }}" alt="{{ $post->title }}">
                                    @endif
                                    <div class="g1-cover__caption">
                                        <span>{{ $catOf($post) }}</span>
                                        <h3>{{ $post->title }}</h3>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    @if($isAr && ($homeDocumentLinks ?? collect())->isNotEmpty())
        <section class="g1-docs">
            <div class="container">
                <div class="g1-docs__panel g1-rise">
                    <!-- <div class="g1-docs__head">
                        <h2>وثائق قانونية</h2>
                        <a class="g1-docs__all" href="{{ route('frontend.documents.index', [$locale]) }}">
                            كل الوثائق
                            <i class="fas fa-arrow-left"></i>
                        </a>
                    </div> -->
                    <nav class="g1-docs__nav" aria-label="أقسام الوثائق">
                        @foreach($homeDocumentLinks as $i => $link)
                            <a href="{{ $link['url'] }}" class="g1-docs__link g1-rise" style="--d: {{ 0.06 + $i * 0.04 }}s">
                                <span class="g1-docs__ico" aria-hidden="true"><i class="fas {{ $link['icon'] }}"></i></span>
                                <span class="g1-docs__label">{{ $link['title'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>
            </div>
        </section>
    @endif

    @if(isset($releasePosts) && $releasePosts->count() > 0)
        <section class="g1-shelf">
            <div class="container">
                <div class="g1-shelf__head g1-rise">
                    <h2>إصدارات أخرى</h2>
                    <!-- <p>{{ $isAr ? 'رفّ الأغلفة — أحدث ما نُشر' : 'Latest covers' }}</p> -->
                </div>
            </div>
            <div class="g1-shelf__scroller">
                <div class="g1-shelf__track">
                    @foreach($releasePosts->take(5) as $i => $post)
                        <a href="{{ $urlOf($post) }}" class="g1-book g1-rise" style="--d: {{ 0.05 + $i * 0.05 }}s">
                            <em>{{ $catOf($post) }}</em>
                            <div class="g1-book__art">
                                @if($post->featured_image)
                                    <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}">
                                @endif
                            </div>
                            <h3>{{ $post->title }}</h3>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Readex+Pro:wght@400;600;700&family=El+Messiri:wght@600;700&display=swap" rel="stylesheet">
<link href="{{ asset('css/home1.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
(function () {
    var nodes = document.querySelectorAll('.g1-rise');
    if (!('IntersectionObserver' in window)) {
        nodes.forEach(function (el) { el.classList.add('is-in'); });
        return;
    }
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-in');
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    nodes.forEach(function (el) { io.observe(el); });
})();
</script>
@endpush
