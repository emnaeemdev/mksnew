@extends('frontend.layouts.app')

@section('title', 'النشرات')

@section('content')
    <section class="page-header">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home') }}">
                            @if(app()->getLocale() == 'ar')
                                الرئيسية
                                <i class="fas fa-home ms-1"></i>
                            @else
                                <i class="fas fa-home me-1"></i>
                                Home
                            @endif
                        </a>
                    </li>
                    <li class="breadcrumb-item active">نشرة الذاكرة القانونية</li>
                </ol>
            </nav>
        </div>
    </section>
    <br>

    <div class="container">
        @if($nashras->count() > 0)
            <div class="row">
                @foreach($nashras as $nashra)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="post-card">
                            <a href="{{ route('frontend.nashras.show', ['locale' => app()->getLocale(), 'nashra' => $nashra->id]) }}"
                               class="text-decoration-none">
                                @if($nashra->featured_image)
                                    <img src="{{ $nashra->featured_image_url }}"
                                         class="post-image post-image-hover"
                                         alt="{{ $nashra->title_ar }}">
                                @else
                                    <img src="{{ asset('images/placeholder.jpg') }}"
                                         class="post-image post-image-hover"
                                         alt="{{ $nashra->title_ar }}">
                                @endif
                            </a>

                            <div class="card-body py-2 px-2">
                                <h6 class="nashra-card-title text-center mb-1">
                                    <a href="{{ route('frontend.nashras.show', ['locale' => app()->getLocale(), 'nashra' => $nashra->id]) }}"
                                       class="text-decoration-none text-dark">
                                        {{ $nashra->title_ar }}
                                    </a>
                                </h6>
                                @if($nashra->subtitle_ar)
                                    <p class="nashra-card-subtitle text-center mb-2 px-2">{{ $nashra->subtitle_ar }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($nashras->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $nashras->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <div class="no-posts">
                <i class="fas fa-search"></i>
                <h4>لا توجد نشرات</h4>
                <p class="text-muted">لم يتم العثور على أي نشرات تطابق معايير البحث الخاصة بك.</p>
                <a href="{{ route('frontend.nashras.index') }}" class="btn btn-primary">
                    <i class="fas fa-refresh me-1"></i>
                    عرض جميع النشرات
                </a>
            </div>
        @endif
    </div>
@endsection
