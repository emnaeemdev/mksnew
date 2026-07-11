@extends('frontend.layouts.app')

@section('title', (app()->isLocale('ar') ? 'الكلمات المفتاحية للمقالات' : 'Post Keywords') . ' - MK Snow')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home', app()->getLocale()) }}">{{ app()->isLocale('ar') ? 'الرئيسية' : 'Home' }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('posts.index', app()->getLocale()) }}">{{ app()->isLocale('ar') ? 'المقالات' : 'Posts' }}</a></li>
            <li class="breadcrumb-item active">{{ app()->isLocale('ar') ? 'الكلمات المفتاحية' : 'Keywords' }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1 text-primary fw-bold">{{ app()->isLocale('ar') ? 'الكلمات المفتاحية للمقالات' : 'Post Keywords' }}</h1>
            <p class="text-muted mb-0">{{ app()->isLocale('ar') ? 'اختر كلمة لعرض المقالات المرتبطة بها' : 'Choose a keyword to view related posts' }}</p>
        </div>
        <form method="get" class="d-flex gap-2" style="min-width:260px">
            <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="{{ app()->isLocale('ar') ? 'بحث...' : 'Search...' }}">
            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>

    @if($keywords->count())
        <div class="d-flex flex-wrap gap-2 mb-4">
            @foreach($keywords as $item)
                <a href="{{ route('posts.keywords.show', [app()->getLocale(), $item->slug]) }}" class="btn btn-outline-primary rounded-pill">
                    {{ $item->name }}
                    <span class="badge text-bg-primary ms-1">{{ $item->usage_count }}</span>
                </a>
            @endforeach
        </div>
        <div class="d-flex justify-content-center">{{ $keywords->links() }}</div>
    @else
        <div class="alert alert-light border">{{ app()->isLocale('ar') ? 'لا توجد كلمات مفتاحية بعد.' : 'No keywords yet.' }}</div>
    @endif
</div>
@endsection
