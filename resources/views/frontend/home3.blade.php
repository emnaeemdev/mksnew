@extends('frontend.layouts.app')

@section('title', (app()->isLocale('ar') ? 'تجربة تصميم 3' : 'Home concept 3') . ' | ' . ($siteName ?? config('app.name')))
@section('body_class', 'concept-home3')

@section('content')
@php
    $locale = app()->getLocale();
    $isAr = app()->isLocale('ar');
    $slider = ($sliderPosts ?? collect())->values();
    $mainPost = $slider->get(0);
    $secTwo = $slider->slice(1, 2)->values();
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

    $channels = [
        ['label' => 'مجلة نوافذ', 'url' => url('/'.$locale.'/posts?category=nawafez'), 'icon' => 'fa-window-restore', 'hint' => $isAr ? '' : 'Magazine'],
        ['label' => 'بودكاست محكمة', 'url' => route('frontend.podcasts.index', [$locale]), 'icon' => 'fa-podcast', 'hint' => $isAr ? '' : 'Podcasts'],
        ['label' => 'نشرة الذاكرة القانونية', 'url' => route('frontend.nashras.index', [$locale]), 'icon' => 'fa-newspaper', 'hint' => $isAr ? '' : 'Bulletins'],
        ['label' => 'هل تعلم', 'url' => $didYouKnowUrl ?? '#', 'icon' => 'fa-lightbulb', 'hint' => $isAr ? '' : 'Did you know'],
    ];
@endphp

<div class="h3" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
    {{-- 1) Featured mosaic only — same layout as agreed --}}
    <section class="h3-stage">
        <div class="container">
            @if($mainPost)
                <div class="h3-mosaic h3-rise">
                    <a class="h3-panel h3-panel--hero" href="{{ $urlOf($mainPost) }}">
                        <div class="h3-panel__media">
                            @if($src = $imgOf($mainPost, true))
                                <img src="{{ $src }}" alt="{{ $mainPost->title }}">
                            @endif
                            <div class="h3-panel__caption">
                                <span>{{ $catOf($mainPost) }}</span>
                                <h2>{{ $mainPost->title }}</h2>
                            </div>
                        </div>
                    </a>

                    @foreach($secTwo as $post)
                        <a class="h3-panel h3-panel--tall" href="{{ $urlOf($post) }}">
                            <div class="h3-panel__media">
                                @if($src = $imgOf($post))
                                    <img src="{{ $src }}" alt="{{ $post->title }}">
                                @endif
                                <div class="h3-panel__caption">
                                    <span>{{ $catOf($post) }}</span>
                                    <h3>{{ $post->title }}</h3>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- 2) Brand strip + channels — different from home1 --}}
    <section class="h3-identity">
        <div class="container h3-identity__grid">
            <div class="h3-identity__brand h3-rise">
                <p class="h3-identity__kicker">{{ $isAr ? 'الذاكرة والمعرفة' : 'Memory & Knowledge' }}</p>
                <h1>{{ $brand }}</h1>
                <p>
                شركة أبحاث مختصة بدراسة تاريخ منظومة العدالة الرسمية في مصر، بشقيها التشريعي والقضائي.
                </p>
            </div>

            <div class="h3-channels h3-rise" style="--d:.1s">
                @foreach($channels as $ch)
                    <a class="h3-channel" href="{{ $ch['url'] }}">
                        <span class="h3-channel__icon"><i class="fas {{ $ch['icon'] }}"></i></span>
                        <span class="h3-channel__text">
                            <strong>{{ $ch['label'] }}</strong>
                            <em>{{ $ch['hint'] }}</em>
                        </span>
                        <i class="fas fa-arrow-left h3-channel__arrow"></i>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 3) Documents as timeline/list — not numbered cards like home1 --}}
    @if($isAr && ($homeDocumentLinks ?? collect())->isNotEmpty())
        <section class="h3-docs">
            <div class="container">
                <div class="h3-docs__layout">


                    <ol class="h3-docs__list">
                        @foreach($homeDocumentLinks as $i => $link)
                            <li class="h3-rise" style="--d: {{ 0.04 + $i * 0.04 }}s">
                                <a href="{{ $link['url'] }}">
                                    <span class="h3-docs__idx">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                    <i class="fas {{ $link['icon'] }}"></i>
                                    <span class="h3-docs__name">{{ $link['title'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                    <header class="h3-docs__intro h3-rise">
                        <h2>أقسام الوثائق</h2>
                        <p>أرشيف الوثائق القانونية على موقع الذاكرة والمعرفة يصل إلى أكثر من 15,000 وثيقة قانونية</p>
                        <a class="h3-docs__all" href="{{ route('frontend.documents.index', [$locale]) }}">
                            {{ $isAr ? 'كل الوثائق' : 'All documents' }}
                            <i class="fas fa-arrow-left"></i>
                        </a>
                    </header>
                </div>
            </div>
        </section>
    @endif

    {{-- 4) Releases as dense editorial grid --}}
    @if(isset($releasePosts) && $releasePosts->count() > 0)
        <section class="h3-pub">
            <div class="container">
                <div class="h3-pub__head h3-rise">
                    <h2>إصدارات أخرى</h2>
                    <!-- <p>{{ $isAr ? 'أحدث ما نُشر من التقارير والأدلة' : 'Latest publications' }}</p> -->
                </div>

                <div class="h3-pub__grid">
                    @foreach($releasePosts->take(4) as $i => $post)
                        <a href="{{ $urlOf($post) }}" class="h3-pub__item h3-rise" style="--d: {{ 0.05 + $i * 0.05 }}s">
                            <div class="h3-pub__cover">
                                @if($post->featured_image)
                                    <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}">
                                @endif
                            </div>
                            <div class="h3-pub__meta">
                                <span>{{ $catOf($post) }}</span>
                                <h3>{{ $post->title }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;600;700&display=swap" rel="stylesheet">
<link href="{{ asset('css/home3.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
(function () {
    var nodes = document.querySelectorAll('.h3-rise');
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
