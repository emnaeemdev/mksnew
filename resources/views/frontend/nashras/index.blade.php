@extends('frontend.layouts.app')

@section('title', 'النشرات')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <!-- عنوان الصفحة -->
            <div class="text-center mb-5">
                <h1 class="display-4 text-primary mb-3">نشرة الذاكرة القانونية</h1>            
            </div>
            
            <!-- تم إزالة شريط البحث والفلاتر بناءً على طلبك -->
            
            <!-- النتائج -->
            @if($nashras->count() > 0)
                <!-- عرض الشبكة (كروت فقط) وكل كارت يعتبر رابط كامل -->
                <div class="row g-4 justify-content-center">
                    @foreach($nashras as $nashra)
                        <div class="col-12 col-sm-6 col-md-4 d-flex justify-content-center">
                            <a href="{{ route('frontend.nashras.show', ['locale' => app()->getLocale(), 'nashra' => $nashra->id]) }}"
                               class="card nashra-card shadow-sm hover-shadow text-reset text-decoration-none">
                                @if($nashra->featured_image)
                                    <img src="{{ $nashra->featured_image_url }}"
                                         alt="{{ $nashra->title_ar }}"
                                         class="nashra-image">
                                @else
                                    <div class="nashra-image placeholder-bg"></div>
                                @endif
                                
                                <!-- Overlay title on hover (same behavior as posts) -->
                                <div class="post-title-overlay">{{ $nashra->title_ar }}</div>

                                <div class="card-body">
                                    <h5 class="card-title text-primary mb-2">{{ $nashra->title_ar }}</h5>
                                    @if($nashra->subtitle_ar)
                                        <p class="card-subtitle text-muted">{{ $nashra->subtitle_ar }}</p>
                                    @endif
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <!-- الترقيم -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $nashras->appends(request()->query())->links() }}
                </div>
            @else
                <!-- لا توجد نتائج -->
                <div class="text-center py-5">
                    <div class="card shadow-sm">
                        <div class="card-body py-5">
                            <i class="fas fa-search fa-4x text-muted mb-4"></i>
                            <h3 class="text-muted">لا توجد نشرات</h3>
                            <p class="text-muted">لم يتم العثور على أي نشرات تطابق معايير البحث الخاصة بك.</p>
                            <a href="{{ route('frontend.nashras.index') }}" class="btn btn-primary">
                                <i class="fas fa-refresh"></i> عرض جميع النشرات
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Hover shadow */
.hover-shadow { transition: box-shadow 0.3s ease; }
.hover-shadow:hover { box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important; }

/* ثابت أبعاد الكروت والصور */
.nashra-card { width: 100%; position: relative; overflow: hidden; }
.nashra-card img.nashra-image, .nashra-card .nashra-image { width: 100% !important; height: 18rem !important; object-fit: cover; display: block; max-width: none !important; }
.placeholder-bg { background-color: #f0f2f5; }

/* نفس ستايل الـ overlay المستخدم في الأقسام الأخرى */
.post-title-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 10px 15px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    text-align: center;
    opacity: 0;
    transition: all 0.3s ease;
    backdrop-filter: blur(5px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    max-width: 80%;
    line-height: 1.3;
}

.nashra-card:hover .post-title-overlay { opacity: 1; }
</style>
@endpush

{{-- تم إزالة سكريبتات الفلاتر لعدم الحاجة إليها --}}