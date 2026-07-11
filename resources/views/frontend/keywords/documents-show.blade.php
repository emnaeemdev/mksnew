@extends('frontend.layouts.app')

@section('title', $keyword->name . ' - ' . (app()->isLocale('ar') ? 'كلمات مفتاحية' : 'Keywords'))

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home', app()->getLocale()) }}">{{ app()->isLocale('ar') ? 'الرئيسية' : 'Home' }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('frontend.documents.keywords.index', app()->getLocale()) }}">{{ app()->isLocale('ar') ? 'الكلمات المفتاحية' : 'Keywords' }}</a></li>
            <li class="breadcrumb-item active">{{ $keyword->name }}</li>
        </ol>
    </nav>

    <div class="mb-4">
        <h1 class="h4 text-primary fw-bold mb-1">{{ $keyword->name }}</h1>
        <p class="text-muted mb-0">
            {{ app()->isLocale('ar') ? 'عدد الوثائق:' : 'Documents:' }}
            <strong>{{ $documents->total() }}</strong>
        </p>
    </div>

    @if($documents->count())
        <div class="list-group mb-4">
            @foreach($documents as $document)
                @php
                    $sectionSlug = optional($document->section)->name_en ?: optional($document->section)->slug;
                    $url = $sectionSlug
                        ? route('content.show', [app()->getLocale(), $sectionSlug, $document->id])
                        : route('frontend.documents.show', [app()->getLocale(), $document]);
                @endphp
                <a href="{{ $url }}" class="list-group-item list-group-item-action py-3">
                    <div class="fw-bold text-primary">{{ $document->title }}</div>
                    <small class="text-muted">
                        {{ optional($document->section)->name }}
                        @if($document->published_at)
                            · {{ $document->published_at->format('Y-m-d') }}
                        @endif
                    </small>
                </a>
            @endforeach
        </div>
        <div class="d-flex justify-content-center">{{ $documents->links() }}</div>
    @else
        <div class="alert alert-light border">{{ app()->isLocale('ar') ? 'لا توجد وثائق تحت هذه الكلمة.' : 'No documents for this keyword.' }}</div>
    @endif
</div>
@endsection
