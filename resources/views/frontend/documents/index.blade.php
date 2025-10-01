@extends('frontend.layouts.app')

@section('title', 'الوثائق')

@section('content')
<div class="container py-5">
    <!-- رأس الصفحة -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="text-center">
                <h1 class="display-4 fw-bold text-primary mb-3">الوثائق</h1>

            </div>
        </div>
    </div>

    <!-- نموذج البحث العام -->
    <div class="row mb-4">
        <div class="col-12">
            <form action="{{ route('frontend.documents.search', [app()->getLocale()]) }}" method="GET" class="card border-0 shadow-sm">
                <div class="card-body p-3 p-md-4">
                    <div class="row g-2 g-md-3 align-items-end">
                        <div class="col-md-6">
                            <label for="search" class="form-label mb-1">ابحث في الوثائق</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="اكتب كلمات البحث...">
                        </div>
                        <div class="col-md-4">
                            <label for="section" class="form-label mb-1">القسم</label>
                            <select name="section" id="section" class="form-select">
                                <option value="">كل الأقسام</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}" {{ request('section') == $section->id ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? $section->name : $section->name_en }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search ms-1"></i>
                                بحث
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- أقسام الوثائق -->
    @if($sections->count() > 0)
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="h3 mb-4">تصفح حسب القسم</h2>
                <div class="row g-4">
                    @foreach($sections as $section)
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 border-0 shadow-sm hover-card">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" 
                                             style="width: 80px; height: 80px;">
                                            <i class="fas fa-folder-open text-primary" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                    <h5 class="card-title mb-3">{{ app()->getLocale() === 'ar' ? $section->name : $section->name_en }}</h5>
                                    @if($section->description)
                                        <p class="card-text text-muted mb-3">{{ Str::limit($section->description, 100) }}</p>
                                    @endif
                                    <div class="d-flex justify-content-center align-items-center mb-3">
                                        <span class="badge bg-primary me-2">{{ $section->documents_count }} وثيقة</span>
      
                                    </div>
                                    @if($section->slug)
                                        <a href="{{ route('frontend.documents.section', [app()->getLocale(), $section->slug]) }}" 
                                           class="btn btn-outline-primary">
                                            استكشاف القسم
                                            <i class="fas fa-arrow-left ms-2"></i>
                                        </a>
                                    @else
                                        <span class="btn btn-outline-secondary disabled">
                                            القسم غير متاح
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif



                                
    
    <!-- الوثائق المميزة -->
    @if($featuredDocuments->count() > 0)
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="h3 mb-4">
                    <i class="fas fa-star text-warning me-2"></i>
                    الوثائق المميزة
                </h2>
                <div class="row g-4">
                    @foreach($featuredDocuments as $document)
                        <div class="col-lg-6">
                            <div class="card h-100 border-0 shadow-sm hover-card">
                                <div class="row g-0 h-100">
                                    <div class="col-12">
                                        <div class="card-body h-100 d-flex flex-column">
                                            <div class="mb-2">
                                                <span class="badge bg-warning text-dark me-2">مميز</span>
                                                <span class="badge bg-primary">{{ app()->getLocale() === 'ar' ? (optional($document->section)->name ?? 'غير مصنّف') : (optional($document->section)->name_en ?? 'Uncategorized') }}</span>
                                            </div>
                                            <h5 class="card-title">
                                                @php
                    $locale = app()->getLocale();
                    $sectionName = optional($document->section)->name_en ?: optional($document->section)->slug;
                    $documentShowUrl = $sectionName
                        ? route('content.show', [$locale, $sectionName, $document->id])
                        : route('content.show', [$locale, 'documents', $document->id]);
                                @endphp
                                <a href="{{ $documentShowUrl }}" 
                                    class="text-decoration-none text-dark">
                                    {{ $document->title }}
                                </a>
                                            </h5>
                                            @if($document->excerpt)
                                                <p class="card-text text-muted flex-grow-1">
                                                    {{ Str::limit($document->excerpt, 120) }}
                                                </p>
                                            @endif
                                            <div class="mt-auto">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <i class="fas fa-eye me-1"></i>
                                                        {{ number_format($document->views_count) }} مشاهدة
                                                    </small>
                                                    <small class="text-muted">
                                                        {{ $document->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    
    <!-- أحدث الوثائق -->
    @if($recentDocuments->count() > 0)
        <div class="row mb-5">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h3 mb-0">
                        <i class="fas fa-clock text-primary me-2"></i>
                        أحدث الوثائق
                    </h2>
                    <a href="{{ route('frontend.documents.search', ['sort' => 'latest']) }}" 
                       class="btn btn-outline-primary">
                        عرض المزيد
                        <i class="fas fa-arrow-left ms-2"></i>
                    </a>
                </div>
                
                <div class="row g-4">
                    @foreach($recentDocuments as $document)
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 border-0 shadow-sm hover-card">

                                
                                <div class="card-body d-flex flex-column">
                                    <div class="mb-2">
                                        <span class="badge bg-primary">{{ app()->getLocale() === 'ar' ? (optional($document->section)->name ?? 'غير مصنّف') : (optional($document->section)->name_en ?? 'Uncategorized') }}</span>
                                        @if($document->is_featured)
                                            <span class="badge bg-warning text-dark">مميز</span>
                                        @endif
                                    </div>
                                    
                                    <h5 class="card-title">
                                        @php
                                            $locale = app()->getLocale();
                                            $sectionName = optional($document->section)->name_en ?: optional($document->section)->slug;
                                            $documentShowUrl = $sectionName
                                                ? route('content.show', [$locale, $sectionName, $document->id])
                                                : route('content.show', [$locale, 'documents', $document->id]);
                                        @endphp
                                        <a href="{{ $documentShowUrl }}" 
                                           class="text-decoration-none text-dark">
                                            {{ Str::limit($document->title, 60) }}
                                        </a>
                                    </h5>
                                    
                                    @if($document->excerpt)
                                        <p class="card-text text-muted flex-grow-1">
                                            {{ Str::limit($document->excerpt, 100) }}
                                        </p>
                                    @endif
                                    @if($document->fieldValues->count() > 0)
                            <div class="mb-3">
                                @foreach($document->fieldValues->take(5) as $fieldValue)
                                    @if($fieldValue->field->type == 'select' || $fieldValue->field->type == 'date' || $fieldValue->field->type == 'text')
                                        <div class="small text-muted mb-1">
                                            <strong>{{ $fieldValue->field->label }}:</strong>
                                            @if($fieldValue->field->type == 'date')
                                                {{ \Carbon\Carbon::parse($fieldValue->value)->format('Y-m-d') }}
                                            @else
                                                {{ $fieldValue->value }}
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                                    
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-eye me-1"></i>
                                                {{ number_format($document->views_count) }}
                                            </small>
                                            <small class="text-muted">
                                             
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    
    <!-- إحصائيات سريعة -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body py-5">
                    <div class="row text-center">
                        <div class="col-md-4 col-6 mb-3 mb-md-0">
                            <div class="h2 fw-bold mb-1">{{ number_format($totalDocuments) }}</div>
                            <div class="text-white-50">إجمالي الوثائق</div>
                        </div>
                        <div class="col-md-4 col-6 mb-3 mb-md-0">
                            <div class="h2 fw-bold mb-1">{{ number_format($totalSections) }}</div>
                            <div class="text-white-50">الأقسام</div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="h2 fw-bold mb-1">{{ number_format($totalViews) }}</div>
                            <div class="text-white-50">إجمالي المشاهدات</div>
                        <!-- </div>
                        <div class="col-md-3 col-6">
                            <div class="h2 fw-bold mb-1">{{ number_format($totalFields) }}</div>
                            <div class="text-white-50">الحقول المخصصة</div>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // تأثيرات hover للبطاقات
    $('.hover-card').hover(
        function() {
            $(this).addClass('shadow-lg').css('transform', 'translateY(-5px)');
        },
        function() {
            $(this).removeClass('shadow-lg').css('transform', 'translateY(0)');
        }
    );
});
</script>
@endpush

@push('styles')
<style>
.hover-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.hover-card:hover {
    transform: translateY(-5px);
}

.card-img-top {
    transition: transform 0.3s ease;
}

.hover-card:hover .card-img-top {
    transform: scale(1.05);
}

.badge {
    font-size: 0.75em;
}

.form-label {
    font-weight: 600;
    color: #495057;
}

.btn-link {
    font-size: 0.9rem;
}

.display-4 {
    font-size: 2.5rem;
}

@media (max-width: 768px) {
    .display-4 {
        font-size: 2rem;
    }
    
    .card-body {
        padding: 1rem;
    }
}

.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.text-white-50 {
    color: rgba(255, 255, 255, 0.5) !important;
}

.border-top {
    border-top: 1px solid #dee2e6 !important;
}

.rounded-start {
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}
</style>
@endpush