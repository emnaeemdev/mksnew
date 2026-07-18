@extends('frontend.layouts.app')

@section('title', (isset($category) ? $category->name : 'جميع المواضيع') . ' - mksegypt')

@section('content')
    
    
    <section class="page-header">
        <div class="container">

            
 <nav aria-label="breadcrumb">
    <ol class="breadcrumb" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
        <li class="breadcrumb-item">
            <a href="{{ route('home') }}">
                @if(app()->getLocale() == 'ar')
                    {{ 'الرئيسية' }}
                    <i class="fas fa-home ms-1"></i>
                @else
                    <i class="fas fa-home me-1"></i>
                    {{ 'Home' }}
                @endif
            </a>
        </li>
        <li class="breadcrumb-item active">{{ isset($category) ? $category->name : 'جميع المواضيع' }}</li>
    </ol>
</nav>
        </div>
    </section>
    <br>
    <div class="container">
            
        
        
    @if($posts->count() > 0)
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


        
        
        @if($posts->hasPages())
        <div class="d-flex justify-content-center">
            {{ $posts->appends(request()->query())->links() }}
        </div>
        @endif
        
        @else
        
        <div class="no-posts">
            <i class="fas fa-search"></i>
            <h4>{{ app()->getLocale() == 'ar' ? 'لا توجد مواضيع' : 'No Topics Found' }}</h4>
            <p class="text-muted">
                @if(request('search'))
                    {{ app()->getLocale() == 'ar' ? 'لم يتم العثور على مواضيع تطابق بحثك.' : 'No topics found matching your search.' }}
                @else
                    {{ app()->getLocale() == 'ar' ? 'لا توجد مواضيع في هذا القسم حالياً.' : 'No topics available in this category at the moment.' }}
                @endif
            </p>
            
            @if(request('search'))
            <a href="{{ route('posts.index', isset($category) ? [app()->getLocale(), 'category' => $category->slug] : [app()->getLocale()]) }}" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>
                {{ app()->getLocale() == 'ar' ? 'عرض جميع المواضيع' : 'View All Topics' }}
            </a>
            @else
            <a href="{{ route('home') }}" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>
                {{ app()->getLocale() == 'ar' ? 'العودة للرئيسية' : 'Back to Home' }}
            </a>
            @endif
        </div>
        @endif
        

    </div>
    
@endsection