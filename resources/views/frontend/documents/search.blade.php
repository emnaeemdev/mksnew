@extends('frontend.layouts.app')

@section('title', 'تصفح الوثائق')
@section('description', 'تصفح مجموعة شاملة من الوثائق والمستندات')

@section('content')
<div class="container py-5">
    <!-- التنقل -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb" dir="rtl">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home') }}">
                            الرئيسية
                            <i class="fas fa-home ms-1"></i>
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('frontend.documents.index') }}">
                            الوثائق
                        </a>
                    </li>
                    <li class="breadcrumb-item active">تصفح الوثائق</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- رأس الصفحة -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-5 fw-bold text-primary mb-3">
                <i class="fas fa-folder-open me-3"></i>
                تصفح الوثائق
            </h1>
            <p class="lead text-muted mb-4">اكتشف مجموعة شاملة من الوثائق مصنفة حسب الأقسام</p>
        </div>
    </div>
    
    <!-- نموذج البحث العام -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('frontend.documents.search', app()->getLocale()) }}" method="GET" id="generalSearchForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-6">
                                <label for="search" class="form-label">كلمة/عبارة البحث</label>
                                <input type="text" id="search" name="search" class="form-control" value="{{ old('search', $searchTerm ?? request('search')) }}" placeholder="اكتب عبارة أو كلمات...">
                            </div>
                            <div class="col-lg-4">
                                <label for="section" class="form-label">القسم</label>
                                <select class="form-select" id="section" name="section">
                                    <option value="">كل الأقسام</option>
                                    @if(isset($sections) && $sections->count())
                                        @foreach($sections as $sec)
                                            <option value="{{ $sec->id }}" {{ (string)request('section') === (string)$sec->id ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? $sec->name : $sec->name_en }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-lg-2 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> بحث</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- نتائج البحث المصنفة --}}
    @if(isset($categorizedResults) && $categorizedResults)
        @php $raw = $categorizedResults['raw'] ?? ''; @endphp
        <div class="row mt-2">
            <div class="col-12">
                <h5 class="mb-3"><i class="fas fa-quote-left ms-1"></i> مطابقة تامة</h5>
                @if(($categorizedResults['phrase'] ?? null) && $categorizedResults['phrase']->count())
                    <div class="row g-3 mb-3">
                        @foreach($categorizedResults['phrase'] as $document)
                            @include('frontend.documents.partials.search-result-card', ['document' => $document, 'matchType' => 'exact'])
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-center mb-4">
                        {{ $categorizedResults['phrase']->appends(request()->except('page_p'))->links() }}
                    </div>
                @else
                    <div class="alert alert-light">لا توجد نتائج مطابقة تمامًا لعبارة "{{ $raw }}"</div>
                @endif
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-12">
                <h5 class="mb-3"><i class="fas fa-check-double ms-1"></i> جميع الكلمات</h5>
                @if(($categorizedResults['all'] ?? null) && $categorizedResults['all']->count())
                    <div class="row g-3 mb-3">
                        @foreach($categorizedResults['all'] as $document)
                            @include('frontend.documents.partials.search-result-card', ['document' => $document, 'matchType' => 'all'])
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-center mb-4">
                        {{ $categorizedResults['all']->appends(request()->except('page_a'))->links() }}
                    </div>
                @else
                    <div class="alert alert-light">لا توجد نتائج تحتوي كل كلمات البحث.</div>
                @endif
            </div>
        </div>

        @if(!empty($categorizedResults['per_word']))
            <div class="row mt-2">
                <div class="col-12">
                    <h5 class="mb-3"><i class="fas fa-check ms-1"></i> كل كلمة على حدة</h5>
                    @foreach($categorizedResults['per_word'] as $word => $page)
                        @if($page->count())
                            <div class="mb-2"><span class="badge bg-info">{{ $word }}</span></div>
                            <div class="row g-3 mb-3">
                                @foreach($page as $document)
                                    @include('frontend.documents.partials.search-result-card', ['document' => $document, 'matchType' => 'any'])
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-center mb-4">
                                {{ $page->appends(request()->except('page_w'.$loop->index))->links() }}
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    <!-- تنبيه إيقاف البحث -->


    <!-- أزرار التنقل -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <a href="{{ route('frontend.documents.index') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-list me-2"></i>
                    جميع الوثائق
                </a>
                <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-home me-2"></i>
                    الصفحة الرئيسية
                </a>
            </div>
        </div>
    </div>

    <!-- الأقسام المتاحة -->
    @if(isset($sections) && $sections->count() > 0)
        <div class="row">
            <div class="col-12">
                <h3 class="text-center mb-4">الأقسام المتاحة</h3>
                <div class="row g-4">
                    @foreach($sections as $section)
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 border-0 shadow-sm hover-card">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-folder-open text-primary" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <h5 class="card-title">{{ app()->getLocale() === 'ar' ? $section->name : $section->name_en }}</h5>
                                    @if($section->description)
                                        <p class="card-text text-muted">{{ Str::limit($section->description, 100) }}</p>
                                    @endif
                                    <div class="mb-3">
                                        <span class="badge bg-primary">{{ $section->documents_count ?? 0 }} وثيقة</span>
                                    </div>
                                    @if($section->slug)
                                        <a href="{{ route('frontend.documents.section', [app()->getLocale(), $section->slug]) }}" 
                                           class="btn btn-outline-primary">
                                            تصفح القسم
                                            <i class="fas fa-arrow-left ms-1"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.hover-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important;
}

.breadcrumb {
    background-color: transparent;
    padding: 0;
    margin-bottom: 1rem;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "/";
    color: #6c757d;
}

.display-5 {
    font-size: 2.5rem;
    line-height: 1.2;
}

@media (max-width: 768px) {
    .display-5 {
        font-size: 2rem;
    }
}

.alert-info {
    border-right: 4px solid #0dcaf0;
}

.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}
</style>
@endpush