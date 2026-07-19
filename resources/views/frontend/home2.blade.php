@extends('frontend.layouts.app')

@section('title', (app()->isLocale('ar') ? 'تجربة تصميم 2 — قاعة المعرفة' : 'Home concept 2') . ' | ' . ($siteName ?? config('app.name')))
@section('body_class', 'concept-home2')

@section('content')
@php
    $locale = app()->getLocale();
    $mainPost = ($sliderPosts ?? collect())->first();
    $sidePosts = ($sliderPosts ?? collect())->skip(1)->take(3)->values();
    $brand = $siteName ?? 'الذاكرة والمعرفة للدراسات';
    $heroImage = $mainPost
        ? ($mainPost->background_image
            ? asset('storage/' . $mainPost->background_image)
            : ($mainPost->featured_image ? asset('storage/' . $mainPost->featured_image) : null))
        : null;
@endphp

<div class="h2-page" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
    {{-- Masthead band --}}
    <section class="h2-mast">
        <div class="container h2-mast__inner">
            <div class="h2-mast__brand h2-rise">
                <span class="h2-mast__mark" aria-hidden="true"></span>
                <h1>{{ $brand }}</h1>
            </div>
            <!-- <p class="h2-mast__tagline h2-rise h2-rise--d1">
                {{ $locale === 'ar'
                    ? 'منصة معرفية للوثائق القانونية والدراسات والإصدارات من مكان واحد.'
                    : 'A knowledge platform for legal documents, studies, and publications.' }}
            </p> -->
        </div>
    </section>

    {{-- Featured mosaic --}}
    @if($mainPost)
        <section class="h2-mosaic">
            <div class="container">
                <div class="h2-mosaic__layout">
                    <a class="h2-feature h2-rise h2-rise--d2"
                       href="{{ route('content.show', [$locale, $mainPost->category->name_en ?: $mainPost->category->slug, $mainPost->id]) }}">
                        <div class="h2-feature__visual" @if($heroImage) style="background-image:url('{{ $heroImage }}')" @endif>
                            <span class="h2-feature__shade"></span>
                        </div>
                        <div class="h2-feature__copy">
                            <span class="h2-pill">
                                {{ $locale === 'ar'
                                    ? ($mainPost->category->name_ar ?? $mainPost->category->name ?? '')
                                    : ($mainPost->category->name_en ?? $mainPost->category->name ?? '') }}
                            </span>
                            <h2>{{ $mainPost->title }}</h2>
                            <span class="h2-feature__cta">
                                {{ $locale === 'ar' ? 'متابعة القراءة' : 'Continue reading' }}
                                <i class="fas fa-arrow-left"></i>
                            </span>
                        </div>
                    </a>

                    <div class="h2-stack">
                        @foreach($sidePosts as $i => $post)
                            @php
                                $img = $post->featured_image
                                    ? asset('storage/' . $post->featured_image)
                                    : ($post->background_image ? asset('storage/' . $post->background_image) : null);
                                $url = route('content.show', [$locale, $post->category->name_en ?: $post->category->slug, $post->id]);
                                $cat = $locale === 'ar'
                                    ? ($post->category->name_ar ?? $post->category->name ?? '')
                                    : ($post->category->name_en ?? $post->category->name ?? '');
                            @endphp
                            <a href="{{ $url }}" class="h2-stack__item h2-rise" style="--d: {{ 0.15 + ($i * 0.08) }}s">
                                <div class="h2-stack__thumb" @if($img) style="background-image:url('{{ $img }}')" @endif></div>
                                <div class="h2-stack__text">
                                    <span>{{ $cat }}</span>
                                    <h3>{{ $post->title }}</h3>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Media / knowledge rails --}}
    <section class="h2-rails">
        <div class="container">
            <div class="h2-rails__grid">
                <a class="h2-rail h2-rail--nawafez" href="{{ url('/ar/posts?category=nawafez') }}">
                    <i class="fas fa-window-restore"></i>
                    <div>
                        <strong>مجلة نوافذ</strong>
                        <!-- <em>{{ $locale === 'ar' ? 'إصدارات المجلة' : 'Magazine issues' }}</em> -->
                    </div>
                </a>
                <a class="h2-rail h2-rail--podcast" href="{{ route('frontend.podcasts.index', [$locale]) }}">
                    <i class="fas fa-podcast"></i>
                    <div>
                        <strong>بودكاست محكمة</strong>
                        <!-- <em>{{ $locale === 'ar' ? 'استمع الآن' : 'Listen now' }}</em> -->
                    </div>
                </a>
                <a class="h2-rail h2-rail--nashra" href="{{ route('frontend.nashras.index', [$locale]) }}">
                    <i class="fas fa-newspaper"></i>
                    <div>
                        <strong>نشرة الذاكرة القانونية</strong>
                        <!-- <em>{{ $locale === 'ar' ? 'النشرات الدورية' : 'Periodicals' }}</em> -->
                    </div>
                </a>
                <a class="h2-rail h2-rail--know" href="{{ $didYouKnowUrl ?? '#' }}">
                    <i class="fas fa-lightbulb"></i>
                    <div>
                        <strong>هل تعلم</strong>
                        <!-- <em>{{ $locale === 'ar' ? 'حقائق سريعة' : 'Quick facts' }}</em> -->
                    </div>
                </a>
            </div>
        </div>
    </section>

    @if(app()->isLocale('ar') && ($homeDocumentLinks ?? collect())->isNotEmpty())
        <section class="h2-vault">
            <div class="container">
                <div class="h2-vault__head">
                    <h2> وثائق قانونية</h2>
                    <!-- <p>اختصارات مباشرة لأهم أقسام الوثائق على المنصة</p> -->
                </div>
                <ul class="h2-vault__list">
                    @foreach($homeDocumentLinks as $link)
                        <li>
                            <a href="{{ $link['url'] }}">
                                <i class="fas {{ $link['icon'] }}"></i>
                                <span>{{ $link['title'] }}</span>
                                <i class="fas fa-chevron-left h2-vault__arrow"></i>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if(isset($releasePosts) && $releasePosts->count() > 0)
        <section class="h2-shelf">
            <div class="container">
                <div class="h2-shelf__head">
                    <h2>إصدارات أخرى</h2>
                    <!-- <p>{{ $locale === 'ar' ? 'رفّ معرفي بأحدث الإصدارات' : 'A shelf of recent publications' }}</p> -->
                </div>
            </div>
            <div class="h2-shelf__track-wrap">
                <div class="h2-shelf__track">
                    @foreach($releasePosts->take(5) as $post)
                        @php
                            $url = route('content.show', [$locale, $post->category->name_en ?: $post->category->slug, $post->id]);
                            $cat = $locale === 'ar'
                                ? (optional($post->category)->name_ar ?? 'غير مصنف')
                                : (optional($post->category)->name_en ?? 'Uncategorized');
                        @endphp
                        <a href="{{ $url }}" class="h2-book">
                            <span class="h2-book__label">{{ $cat }}</span>
                            <div class="h2-book__cover">
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
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<link href="{{ asset('css/home2.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
(function () {
    var nodes = document.querySelectorAll('.h2-rise, .h2-rail, .h2-book, .h2-vault__list li');
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
