@extends('frontend.layouts.app')

@section('title', $keyword->name . ' - ' . (app()->isLocale('ar') ? 'كلمات مفتاحية' : 'Keywords'))

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home', app()->getLocale()) }}">{{ app()->isLocale('ar') ? 'الرئيسية' : 'Home' }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('posts.keywords.index', app()->getLocale()) }}">{{ app()->isLocale('ar') ? 'الكلمات المفتاحية' : 'Keywords' }}</a></li>
            <li class="breadcrumb-item active">{{ $keyword->name }}</li>
        </ol>
    </nav>

    <div class="mb-4">
        <h1 class="h4 text-primary fw-bold mb-1">{{ $keyword->name }}</h1>
        <p class="text-muted mb-0">
            {{ app()->isLocale('ar') ? 'عدد المقالات:' : 'Posts:' }}
            <strong>{{ $posts->total() }}</strong>
        </p>
    </div>

    @if($posts->count())
        <div class="row">
            @foreach($posts as $post)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="post-card">
                        <a href="{{ route('content.show', [app()->getLocale(), $post->category->slug, $post->id]) }}" class="text-decoration-none">
                            @if($post->featured_image)
                                <img src="{{ asset('storage/' . $post->featured_image) }}" class="post-image post-image-hover" alt="{{ $post->title }}">
                            @else
                                <img src="{{ asset('images/placeholder.jpg') }}" class="post-image post-image-hover" alt="{{ $post->title }}">
                            @endif
                        </a>
                        <div class="card-body">
                            <h6 class="post-title-arcive text-center">
                                <a href="{{ route('content.show', [app()->getLocale(), $post->category->slug, $post->id]) }}" class="text-decoration-none text-dark">
                                    {{ $post->title }}
                                </a>
                            </h6>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="d-flex justify-content-center">{{ $posts->links() }}</div>
    @else
        <div class="alert alert-light border">{{ app()->isLocale('ar') ? 'لا توجد مقالات تحت هذه الكلمة.' : 'No posts for this keyword.' }}</div>
    @endif
</div>
@endsection
