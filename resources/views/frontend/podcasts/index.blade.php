@extends('frontend.layouts.app')

@section('title', $title ?? (app()->isLocale('ar') ? 'بودكاست محكمة' : 'Court Podcast'))
@section('meta')
    <meta name="description" content="{{ $description ?? '' }}">
@endsection

@section('styles')
<style>
.podcast-card{box-shadow:0 2px 10px rgba(0,0,0,.08);border-radius:12px;overflow:hidden;transition:transform .2s;background:#fff}
.podcast-card:hover{transform:translateY(-4px)}
.podcast-cover{width:100%;height:400px;object-fit:cover;background:#f7f7f7}
.podcast-body{padding:16px}
.podcast-title{font-weight:700;font-size:1.05rem;color:#222;margin-bottom:8px}
.podcast-meta{color:#777;font-size:.85rem}
.search-input{border-radius:10px}

/* جعل البطاقات بنفس الارتفاع */

.row.g-4 > [class*="col-"] > a > .podcast-card{height:100%}
</style>
@endsection

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 m-0">{{ app()->isLocale('ar') ? 'بودكاست محكمة' : 'Court Podcast' }}</h1>
        <form method="get" class="d-flex" style="gap:8px;">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ app()->isLocale('ar') ? 'ابحث في الحلقات...' : 'Search episodes...' }}" class="form-control search-input">
            <button class="btn btn-primary">{{ app()->isLocale('ar') ? 'بحث' : 'Search' }}</button>
        </form>
    </div>

    @if($podcasts->count() === 0)
        <div class="alert alert-info">{{ app()->isLocale('ar') ? 'لا توجد حلقات حالياً.' : 'No episodes yet.' }}</div>
    @else
        <div class="row g-4">
            @foreach($podcasts as $item)
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="{{ route('frontend.podcasts.show', [app()->getLocale(), $item->id]) }}" class="text-decoration-none">
                        <div class="podcast-card">
                            <img class="podcast-cover" src="{{ $item->cover_image_path ? asset('storage/'.$item->cover_image_path) : 'https://via.placeholder.com/600x360?text=Podcast' }}" alt="{{ $item->title }}">
                            <div class="podcast-body">
                                <div class="podcast-title">{{ $item->title }}</div>
                                <div class="podcast-meta">
                                    <i class="fa-solid fa-calendar"></i>
                                    {{ optional($item->published_at)->format('Y-m-d') ?? '' }}
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $podcasts->links() }}
        </div>
    @endif
</div>
@endsection