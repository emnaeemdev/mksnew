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
                                            <option value="{{ $sec->id }}" {{ (string)request('section') === (string)$sec->id ? 'selected' : '' }}>
                                                {{ app()->getLocale() === 'ar' ? $sec->name : $sec->name_en }} ({{ $sec->published_documents_count }})
                                            </option>
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

        @php
            $countForFilters = 0;
            if (!empty($categorizedResults['unique_total'])) {
                $countForFilters = (int) $categorizedResults['unique_total'];
            } else {
                // احتياط: حساب يدوي في حال عدم توفر unique_total
                $phraseIds = collect($categorizedResults['phrase'] ?? [])->pluck('id')->all();
                $allIds = collect($categorizedResults['all'] ?? [])->pluck('id')->all();
                $perWordIds = [];
                foreach (($categorizedResults['per_word'] ?? []) as $page) {
                    $perWordIds = array_merge($perWordIds, $page->pluck('id')->all());
                }
                $countForFilters = count(array_unique(array_merge($phraseIds, $allIds, $perWordIds)));
            }
        @endphp

        {{-- <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-info" role="alert">
                    تم العثور على <strong>{{ $countForFilters }}</strong> وثيقة مع الفلاتر المحددة
                </div>
            </div>
        </div> --}}

        {{-- تبويبات النتائج --}}
        @php
            $activeTab = request('tab');
            if(!$activeTab) {
                $firstWordTab = null;
                $wi = 0;
                foreach(($categorizedResults['per_word'] ?? []) as $w => $page) {
                    if(!$firstWordTab && $page->count()) { $firstWordTab = 'word-'.$wi; }
                    $wi++;
                }
                $activeTab = (($categorizedResults['phrase'] ?? null) && $categorizedResults['phrase']->count()) ? 'phrase'
                    : (($categorizedResults['all'] ?? null) && $categorizedResults['all']->count() ? 'all'
                    : ($firstWordTab ?: 'phrase'));
            }
            $baseQuery = request()->except(['page','page_p','page_a','page_any']);
            $phraseTotal = ($categorizedResults['phrase'] ?? null) ? $categorizedResults['phrase']->total() : 0;
            $allTotal = ($categorizedResults['all'] ?? null) ? $categorizedResults['all']->total() : 0;
        @endphp
        <div class="row mt-3" id="results-tabs">
            <div class="col-12">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <button id="tab-link-phrase" class="nav-link {{ $activeTab=='phrase' ? 'active' : '' }}" data-bs-toggle="tab" type="button" role="tab" data-bs-target="#tab-phrase" aria-controls="tab-phrase">مطابقة تامة <span class="badge rounded-pill bg-secondary ms-1">{{ $phraseTotal }}</span></button>
                    </li>
                    <li class="nav-item">
                        <button id="tab-link-all" class="nav-link {{ $activeTab=='all' ? 'active' : '' }}" data-bs-toggle="tab" type="button" role="tab" data-bs-target="#tab-all" aria-controls="tab-all">جميع الكلمات <span class="badge rounded-pill bg-secondary ms-1">{{ $allTotal }}</span></button>
                    </li>
                    @php $tokensCount = isset($categorizedResults['tokens']) && is_array($categorizedResults['tokens']) ? count($categorizedResults['tokens']) : 0; @endphp
@if(!empty($categorizedResults['per_word']) && $tokensCount > 1)
@foreach($categorizedResults['per_word'] as $word => $page)
                            <li class="nav-item">
                                <button id="tab-link-word-{{ $loop->index }}" class="nav-link {{ $activeTab=='word-'.$loop->index ? 'active' : '' }}" data-bs-toggle="tab" type="button" role="tab" data-bs-target="#tab-word-{{ $loop->index }}" aria-controls="tab-word-{{ $loop->index }}">
                                    نتائج بها كلمة <b style="color: rgb(124, 190, 86);">"{{ $word }}"</b> <span class="badge rounded-pill bg-secondary ms-1">{{ $page->total() }}</span>
                                </button>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>
        </div>

        <div class="tab-content">
        <div id="tab-phrase" class="tab-pane fade row mt-2 {{ $activeTab=='phrase' ? 'show active' : '' }}" role="tabpanel" aria-labelledby="tab-link-phrase">
            <div class="col-12">
                <h5 class="mb-3"><i class="fas fa-quote-left ms-1"></i> مطابقة تامة</h5>
                @if(($categorizedResults['phrase'] ?? null) && $categorizedResults['phrase']->count())
                    <div class="row g-3 mb-3">
                        @foreach($categorizedResults['phrase'] as $document)
                            @include('frontend.documents.partials.search-result-card', ['document' => $document, 'matchType' => 'exact', 'rawSearch' => $raw])
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-center mb-4">
                        {{ $categorizedResults['phrase']->appends(array_merge($baseQuery, ['tab' => 'phrase']))->links() }}
                    </div>
                @else
                    <div class="alert alert-light my-2 py-2">لا توجد نتائج مطابقة تمامًا لعبارة "{{ $raw }}"</div>
                @endif
            </div>
        </div>

        <div id="tab-all" class="tab-pane fade row mt-2 {{ $activeTab=='all' ? 'show active' : '' }}" role="tabpanel" aria-labelledby="tab-link-all">
            <div class="col-12">
                <h5 class="mb-3"><i class="fas fa-check-double ms-1"></i> جميع الكلمات</h5>
                @if(($categorizedResults['all'] ?? null) && $categorizedResults['all']->count())
                    <div class="row g-3 mb-3">
                        @foreach($categorizedResults['all'] as $document)
                            @include('frontend.documents.partials.search-result-card', ['document' => $document, 'matchType' => 'all', 'tokens' => ($categorizedResults['tokens'] ?? [])])
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-center mb-4">
                        {{ $categorizedResults['all']->appends(array_merge($baseQuery, ['tab' => 'all']))->links() }}
                    </div>
                @else
                    <div class="alert alert-light my-2 py-2">لا توجد نتائج تحتوي كل كلمات البحث.</div>
                @endif
            </div>
        </div>

        @if(!empty($categorizedResults['per_word']))
            @php
                $tokensCount = isset($categorizedResults['tokens']) && is_array($categorizedResults['tokens']) ? count($categorizedResults['tokens']) : 0;
            @endphp
            @foreach($categorizedResults['per_word'] as $word => $page)
                <div id="tab-word-{{ $loop->index }}" class="tab-pane fade row mt-2 {{ $activeTab=='word-'.$loop->index ? 'show active' : '' }}" role="tabpanel" aria-labelledby="tab-link-word-{{ $loop->index }}">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="fas fa-check ms-1"></i> نتائج بها كلمة "{{ $word }}"</h5>
                        @if($page->count())
                            <div class="row g-3 mb-3">
                                @foreach($page as $document)
                                    @include('frontend.documents.partials.search-result-card', ['document' => $document, 'matchType' => 'any', 'word' => $word])
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-center mb-4">
                                {{ $page->appends(array_merge(request()->except('page_w'.$loop->index), ['tab' => 'word-'.$loop->index]))->links() }}
                            </div>
                        @else
                            <div class="alert alert-light my-2 py-2">لا توجد نتائج تحتوي كلمة "{{ $word }}".</div>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
        </div>

        {{-- تبويبات النتائج (نهاية الصفحة) --}}
        {{-- <div class="row mt-4" id="results-tabs-bottom">
            <div class="col-12">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab=='phrase' ? 'active' : '' }}" data-bs-toggle="tab" role="tab" href="#tab-phrase" aria-controls="tab-phrase">مطابقة تامة <span class="badge rounded-pill bg-secondary ms-1">{{ $phraseTotal }}</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab=='all' ? 'active' : '' }}" data-bs-toggle="tab" role="tab" href="#tab-all" aria-controls="tab-all">جميع الكلمات <span class="badge rounded-pill bg-secondary ms-1">{{ $allTotal }}</span></a>
                    </li>
                    @php $tokensCount = isset($categorizedResults['tokens']) && is_array($categorizedResults['tokens']) ? count($categorizedResults['tokens']) : 0; @endphp
@if(!empty($categorizedResults['per_word']) && $tokensCount > 1)
    @foreach($categorizedResults['per_word'] as $word => $page)
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab=='word-'.$loop->index ? 'active' : '' }}" data-bs-toggle="tab" role="tab" href="#tab-word-{{ $loop->index }}" aria-controls="tab-word-{{ $loop->index }}">
                                    نتائج بها كلمة "{{ $word }}" <span class="badge rounded-pill bg-secondary ms-1">{{ $page->total() }}</span>
                                </a>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>
        </div> --}}
    @endif

    <!-- تنبيه إيقاف البحث -->

<hr>
<br>
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

@push('scripts')
<script>
    (function(){
        function setActiveInBothNavs(target){
            var links = document.querySelectorAll('[data-bs-toggle="tab"]');
            links.forEach(function(link){
                var href = link.getAttribute('data-bs-target');
                if(href === target){
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        }
        function updateUrlTabParam(tab){
            var url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            history.replaceState(null, '', url.toString());
        }
        document.addEventListener('shown.bs.tab', function (event) {
            var target = event.target.getAttribute('data-bs-target'); // #tab-*
            var tab = target.replace('#tab-','');
            setActiveInBothNavs(target);
            updateUrlTabParam(tab);
        });
    })();
</script>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabLinks = document.querySelectorAll('#results-tabs .nav-link, #results-tabs-bottom .nav-link');
        tabLinks.forEach(btn => {
            btn.addEventListener('click', function(e) {
                // منع أي تمرير تلقائي
                e.preventDefault();
                // تفعيل التبويب عبر Bootstrap بدون تغيير موضع التمرير
                const target = this.getAttribute('data-bs-target');
                if (target) {
                    const tabTrigger = new bootstrap.Tab(this);
                    tabTrigger.show();
                }
                // إيقاف أي تغيير في hash الذي قد يسبب سكرول
                history.replaceState(null, '', window.location.pathname + window.location.search);
            }, { passive: false });
        });
    });
</script>
@endpush