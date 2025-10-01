
@extends('frontend.layouts.app')

@section('title', (app()->getLocale() === 'ar' ? $section->name : $section->name_en) . ' - الوثائق')

@section('content')
<div class="container">
    <!-- رأس القسم -->
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home', app()->getLocale()) }}">
                            @if(app()->getLocale() == 'ar')
                                الرئيسية
                                <i class="fas fa-home ms-1"></i>
                            @else
                                <i class="fas fa-home me-1"></i>
                                Home
                            @endif
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('frontend.documents.index') }}">
                            {{ app()->getLocale() == 'ar' ? 'الوثائق' : 'Documents' }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active">{{ app()->getLocale() === 'ar' ? $section->name : $section->name_en }}</li>
                </ol>
            </nav>
            <br>
            <div class="text-center">
                <div class="mb-1">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" 
                         style="width: 30px; height: 30px;">
                        <i class="fas fa-folder-open text-primary" style="font-size: 1rem;"></i>
                    </div>
                </div>
                <h5 class="fw-bold text-primary mb-3">{{ app()->getLocale() === 'ar' ? $section->name : $section->name_en }}</h5>
                @if($section->description)
                    <p class="lead text-muted mb-4">{{ $section->description }}</p>
                @endif
                
                <div class="d-flex justify-content-center align-items-center gap-3 mb-4">
                    @php
                        $docsCountBadge = isset($documents)
                            ? $documents->total()
                            : ((isset($categorizedResults) && $categorizedResults && isset($categorizedResults['phrase'], $categorizedResults['all']))
                                ? ($categorizedResults['phrase']->total() + $categorizedResults['all']->total())
                                : 0);
                    @endphp
                    <span class="badge bg-primary fs-6">{{ $docsCountBadge }} وثيقة</span>
                   
                </div>
            </div>
        </div>
    </div>
    
    <!-- الفلترة والبحث -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    @if($section && $section->slug)
                        <form action="{{ route('frontend.documents.section', [app()->getLocale(), $section->slug]) }}" method="GET" id="filterForm">
                    @else
                        <form action="{{ route('frontend.documents.index') }}" method="GET" id="filterForm">
                    @endif
                        <div class="row g-4 align-items-end">
                            <!-- مربع البحث -->
                            <div class="col-lg-4">
                                <label for="search" class="form-label">
                                    @if(request('section_select') === 'all')
                                        البحث في جميع الأقسام
                                    @else
                                        البحث في {{ app()->getLocale() === 'ar' ? $section->name : $section->name_en }}
                                    @endif
                                </label>
                                <input type="text" id="search" name="search" class="form-control" value="{{ old('search', $searchTerm ?? request('search')) }}" placeholder="اكتب عبارة أو كلمات...">
                            </div>

    
                            
                            <!-- الترتيب -->
                            <div class="col-lg-2">
                                <label for="sort" class="form-label">الترتيب</label>
                                <select class="form-select" id="sort" name="sort" onchange="document.getElementById('filterForm').submit()">
                                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>الأحدث</option>
                                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>الأقدم</option>
                                    <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>العنوان</option>
                                    <option value="views" {{ request('sort') == 'views' ? 'selected' : '' }}>الأكثر مشاهدة</option>
                                </select>
                            </div>

                            
                            <!-- عدد النتائج -->
                            <div class="col-lg-2">
                                <label for="per_page" class="form-label">عدد النتائج</label>
                                <select class="form-select" id="per_page" name="per_page" onchange="document.getElementById('filterForm').submit()">
                                    <option value="12" {{ request('per_page') == '12' ? 'selected' : '' }}>12</option>
                                    <option value="24" {{ request('per_page') == '24' ? 'selected' : '' }}>24</option>
                                    <option value="48" {{ request('per_page') == '48' ? 'selected' : '' }}>48</option>
                                </select>
                            </div>
                                                    <!-- اختيار القسم -->
                            <div class="col-4">
                                <label for="section_select" class="form-label">القسم</label>
                                <select class="form-select" id="section_select" name="section_select">
                                    <option value="all" {{ request('section_select') === 'all' ? 'selected' : '' }}>كل الأقسام</option>
                                    @if(isset($allSections) && $allSections->count())
                                        @php $selectedSection = request('section_select'); @endphp
                                        @foreach($allSections as $sec)
                                            <option value="{{ $sec->id }}" {{ ($selectedSection !== null ? (string)$selectedSection === (string)$sec->id : (isset($section) && $section->id === $sec->id)) ? 'selected' : '' }}>
                                                {{ $sec->name }} ({{ $sec->published_docs_count ?? ($sec->published_documents_count ?? $sec->publishedDocumentsCount ?? $sec->published_documents_count ?? 0) }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- أزرار الإجراءات -->
                            <!-- <div class="col-lg-2">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> بحث</button>
                                    @if(request()->hasAny(['search', 'sort', 'per_page']) || request()->except(['page']))
                                        @if($section && $section->slug)
                                            <a href="{{ route('frontend.documents.section', [app()->getLocale(), $section->slug]) }}" class="btn btn-outline-danger">
                                                <i class="fas "fa-times"></i> مسح الفلاتر
                                            </a>
                                        @else
                                            <a href="{{ route('frontend.documents.index') }}" class="btn btn-outline-danger">
                                                <i class="fas fa-times"></i> مسح الفلاتر
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </div> -->
                        </div>
                        
                        <!-- فلترة الحقول المخصصة -->
                        @if($customFields->count() > 0)
                            <div class="mt-4">
                                <div class="border-top pt-4">
                                    <h6 class="mb-3">فلترة متقدمة</h6>
                                    <div class="row g-3">
                                        @foreach($customFields as $field)
                                            <div class="col-lg-4 col-md-6">
                                                <label for="field_{{ $field->id }}" class="form-label">{{ $field->label }}</label>
                                                
                                                @switch($field->type)
                                                    @case('select')
                                                        <select class="form-select" id="field_{{ $field->id }}" name="fields[{{ $field->id }}]" onchange="document.getElementById('filterForm').submit()">
                                                            <option value="">جميع القيم</option>
                                                            @php
                                                                $selectedValue = request("fields.{$field->id}");
                                                                $hasActiveFilters = !empty(request('fields'));
                                                            @endphp
                                                            @if($field->options)
                                                                @foreach($field->options as $option)
                                                                    @php
                                                                        $count = $fieldCounts[$field->id][$option] ?? null;
                                                                        $showOption = !$hasActiveFilters || (!is_null($count) && (int)$count > 0) || ($selectedValue === $option);
                                                                    @endphp
                                                                    @if($showOption)
                                                                        <option value="{{ $option }}" 
                                                                                {{ $selectedValue === $option ? 'selected' : '' }}>
                                                                            {{ $option }}
                                                                            @if(!is_null($count))
                                                                                ({{ $count }})
                                                                            @endif
                                                                        </option>
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        @break
                                                        
                                                    @case('date')
                                                        <div class="row g-2">
                                                            <div class="col-4">
                                                                <select class="form-select form-select-sm" name="fields[{{ $field->id }}][day]" onchange="document.getElementById('filterForm').submit()">
                                                                    <option value="">اليوم</option>
                                                                    @if(isset($fieldCounts[$field->id]['day']))
                                                                        @foreach($fieldCounts[$field->id]['day'] as $day => $count)
                                                                            <option value="{{ sprintf('%02d', $day) }}" 
                                                                                    {{ request("fields.{$field->id}.day") == sprintf('%02d', $day) ? 'selected' : '' }}>
                                                                                {{ $day }} ({{ $count }})
                                                                            </option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                            <div class="col-4">
                                                                <select class="form-select form-select-sm" name="fields[{{ $field->id }}][month]" onchange="document.getElementById('filterForm').submit()">
                                                                    <option value="">الشهر</option>
                                                                    @php
                                                                        $months = [
                                                                            '01' => 'يناير', '02' => 'فبراير', '03' => 'مارس', '04' => 'أبريل',
                                                                            '05' => 'مايو', '06' => 'يونيو', '07' => 'يوليو', '08' => 'أغسطس',
                                                                            '09' => 'سبتمبر', '10' => 'أكتوبر', '11' => 'نوفمبر', '12' => 'ديسمبر'
                                                                        ];
                                                                    @endphp
                                                                    @if(isset($fieldCounts[$field->id]['month']))
                                                                        @foreach($fieldCounts[$field->id]['month'] as $monthNum => $count)
                                                                            <option value="{{ $monthNum }}" 
                                                                                    {{ request("fields.{$field->id}.month") == $monthNum ? 'selected' : '' }}>
                                                                                {{ $months[$monthNum] ?? $monthNum }} ({{ $count }})
                                                                            </option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                            <div class="col-4">
                                                                <select class="form-select form-select-sm" name="fields[{{ $field->id }}][year]" onchange="document.getElementById('filterForm').submit()">
                                                                    <option value="">السنة</option>
                                                                    @if(isset($fieldCounts[$field->id]['year']))
                                                                        @foreach($fieldCounts[$field->id]['year'] as $year => $count)
                                                                            <option value="{{ $year }}" 
                                                                                    {{ request("fields.{$field->id}.year") == $year ? 'selected' : '' }}>
                                                                                {{ $year }} ({{ $count }})
                                                                            </option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </div>
                                                        @break
                                                        
                                                    @case('number')
                                                        <div class="row g-2">
                                                            <div class="col-6">
                                                                <input type="number" 
                                                                       class="form-control form-control-sm" 
                                                                       name="fields[{{ $field->id }}][min]" 
                                                                       value="{{ request("fields.{$field->id}.min") }}"
                                                                       placeholder="الحد الأدنى">
                                                            </div>
                                                            <div class="col-6">
                                                                <input type="number" 
                                                                       class="form-control form-control-sm" 
                                                                       name="fields[{{ $field->id }}][max]" 
                                                                       value="{{ request("fields.{$field->id}.max") }}"
                                                                       placeholder="الحد الأقصى">
                                                            </div>
                                                        </div>
                                                        @break
                                                        
                                                    @default
                                                        <input type="text" 
                                                               class="form-control" 
                                                               id="field_{{ $field->id }}" 
                                                               name="fields[{{ $field->id }}]" 
                                                               value="{{ request("fields.{$field->id}") }}"
                                                               placeholder="ابحث في {{ $field->label }}...">
                                                @endswitch
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    <div class="mt-3 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-filter"></i> تطبيق الفلاتر
                                        </button>
                                        @if($section && $section->slug)
                                            <a href="{{ route('frontend.documents.section', [app()->getLocale(), $section->slug]) }}" 
                                               class="btn btn-outline-secondary">
                                                <i class="fas fa-times"></i> مسح الفلاتر
                                            </a>
                                        @else
                                            <a href="{{ route('frontend.documents.index') }}" 
                                               class="btn btn-outline-secondary">
                                                <i class="fas fa-times"></i> مسح الفلاتر
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- عرض إحصائيات النتائج عند استخدام الفلاتر فقط -->
    @if(request('fields'))
        <div class="row mb-4 search-results">
            <div class="col-12">
                <div class="alert alert-info d-flex align-items-center">
                    <i class="fas fa-info-circle me-2"></i>
                    <div>
                        @php
                            $countForFilters = 0;
                            if (isset($categorizedResults) && $categorizedResults) {
                                $countForFilters += isset($categorizedResults['phrase']) ? $categorizedResults['phrase']->total() : 0;
                                $countForFilters += isset($categorizedResults['all']) ? $categorizedResults['all']->total() : 0;
                                if (!empty($categorizedResults['per_word']) && is_array($categorizedResults['per_word'])) {
                                    foreach ($categorizedResults['per_word'] as $pg) {
                                        $countForFilters += $pg->total();
                                    }
                                }
                            } else {
                                $countForFilters = isset($documents) ? $documents->total() : 0;
                            }
                        @endphp
                        تم العثور على <strong>{{ $countForFilters }}</strong> وثيقة
                        مع الفلاتر المحددة
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- نتائج البحث المصنفة (داخل .container) --}}
    @if(isset($categorizedResults) && $categorizedResults)
        @php $raw = $categorizedResults['raw'] ?? ''; @endphp

        <div class="row mt-4">
            <div class="col-12">
                <h5 class="mb-3"><i class="fas fa-quote-left ms-1"></i> وثائق مطابقة  تمامًا لجملة البحث</h5>
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
                <h5 class="mb-3"><i class="fas fa-check-double ms-1"></i>وثائق بها جميع كلمات البحث</h5>
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
            <h5 class="mb-3"><i class="fas fa-check ms-1"></i>وثائق بها كلمة واحدة من البحث</h5>
            
            <!-- متغير لفحص إذا كان فيه نتائج لأي كلمة -->
            @php $noResults = true; @endphp
            
            @foreach($categorizedResults['per_word'] as $word => $page)
                @if($page->count()) 
                    @php $noResults = false; @endphp <!-- إذا كانت فيه نتائج، خلي المتغير false -->
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
            
            <!-- عرض الرسالة فقط لو مفيش أي نتائج -->
            @if($noResults)
                <div class="alert alert-light">لا توجد نتائج تحتوي كلمة واحدة من جملة البحث.</div>
            @endif
        </div>
    </div>
@endif

        
    @endif

    <!-- قائمة الوثائق -->
    @if(isset($documents))
    <div class="documents-container">
    @if($documents->count() > 0)
        <!-- العرض التقليدي -->
        <div class="row g-4 mb-5">
    @foreach($documents as $document)
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('content.show', [app()->getLocale(), $section->name_en ?: $section->slug, $document->id]) }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <!-- <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                         style="height: 200px;">
                        <i class="fas fa-file-alt text-muted" style="font-size: 3rem;"></i>
                    </div> -->

                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            @if($document->is_featured)
                                <span class="badge bg-warning text-dark me-1">مميز</span>
                            @endif
                            <span class="badge bg-primary">{{ app()->getLocale() === 'ar' ? $section->name : $section->name_en }}</span>
                        </div>

                        <h5 class="card-title">
                            {{ Str::limit($document->title, 60) }}
                        </h5>

                        @if($document->excerpt)
                            <p class="card-text text-muted flex-grow-1">
                                {{ Str::limit($document->excerpt, 100) }}
                            </p>
                        @endif

                        <!-- الحقول المخصصة المهمة -->
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

                        <!-- <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-eye me-1"></i>
                                    {{ number_format($document->views_count) }}
                                </small>
                                <small class="text-muted">
                                    {{ $document->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </div> -->
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

        
        <!-- التصفح -->
        @if($documents->hasPages())
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-center">
                        {{ $documents->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        @endif
    @else
        <!-- لا توجد وثائق -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-search text-muted" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="text-muted mb-3">لا توجد وثائق</h3>
                    @if(request('fields'))
                        <p class="text-muted mb-4">لم يتم العثور على وثائق تطابق معايير الفلترة المحددة.</p>
                        @if($section && $section->slug)
                            <a href="{{ route('frontend.documents.section', [app()->getLocale(), $section->slug]) }}" 
                               class="btn btn-primary">
                        @else
                            <a href="{{ route('frontend.documents.index') }}" 
                               class="btn btn-primary">
                        @endif
                            <i class="fas fa-times me-2"></i>
                            مسح الفلاتر
                        </a>
                    @else
                        <p class="text-muted mb-4">لا توجد وثائق في هذا القسم حالياً.</p>
                        <a href="{{ route('frontend.documents.index') }}" 
                           class="btn btn-primary">
                            <i class="fas fa-arrow-right me-2"></i>
                            تصفح جميع الوثائق
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
    </div> <!-- إغلاق documents-container -->
    @endif
</div>

@push('scripts')
<script>
// نظام الفلاتر بدون AJAX - تحديث الصفحة مباشرة
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filterForm');
    if (!form) return;
    
    // مستمع للتغييرات على القوائم المنسدلة - إرسال النموذج فوراً
    form.addEventListener('change', function(e) {
        const target = e.target;
        
        // التحقق من أن العنصر المتغير هو قائمة منسدلة للحقول المخصصة أو عناصر أخرى
        if (target.tagName === 'SELECT') {
            // إرسال النموذج مباشرة عند تغيير أي قائمة منسدلة
            form.submit();
        }
    });
    
    // زر مسح الفلاتر
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            // مسح جميع قيم النموذج
            Array.from(form.elements).forEach(element => {
                if (element.name && element.name.startsWith('fields[')) {
                    element.value = '';
                }
            });
            
            // مسح قيمة البحث أيضاً
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.value = '';
            }
            
            // إعادة توجيه إلى الصفحة بدون فلاتر
            window.location.href = window.location.pathname;
        });
    }
    
    // دالة لتهيئة تأثيرات hover
    function initHoverEffects() {
        const hoverCards = document.querySelectorAll('.hover-card');
        hoverCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.classList.add('shadow-lg');
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.classList.remove('shadow-lg');
                this.style.transform = 'translateY(0)';
            });
        });
    }
    
    // تهيئة تأثيرات hover عند تحميل الصفحة
    initHoverEffects();
});

// تأثيرات hover للبطاقات
$(document).ready(function() {
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
    margin-bottom: 0.5rem;
}



.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.border-top {
    border-top: 1px solid #dee2e6 !important;
}

.fs-6 {
    font-size: 1rem !important;
}

.form-control-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.alert-info {
    border-right: 4px solid #0dcaf0;
}

.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    color: #007bff;
    border: 1px solid #dee2e6;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // جعل القوائم المنسدلة تحدث الصفحة فوراً
    const dropdowns = document.querySelectorAll('#sort, #per_page, select[name^="fields["][name$="]"]');
    
    dropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
    
    // جعل قوائم التاريخ المنسدلة تحدث الصفحة فوراً
    const dateDropdowns = document.querySelectorAll('select[name*="[day]"], select[name*="[month]"], select[name*="[year]"]');
    
    dateDropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
});
</script>

<style>
.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}
</style>
@endpush



@push('scripts')
<script>
// نظام الفلاتر بدون AJAX - تحديث الصفحة مباشرة
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filterForm');
    if (!form) return;
    
    // مستمع للتغييرات على القوائم المنسدلة - إرسال النموذج فوراً
    form.addEventListener('change', function(e) {
        const target = e.target;
        
        // التحقق من أن العنصر المتغير هو قائمة منسدلة للحقول المخصصة أو عناصر أخرى
        if (target.tagName === 'SELECT') {
            // إرسال النموذج مباشرة عند تغيير أي قائمة منسدلة
            form.submit();
        }
    });
    
    // زر مسح الفلاتر
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            // مسح جميع قيم النموذج
            Array.from(form.elements).forEach(element => {
                if (element.name && element.name.startsWith('fields[')) {
                    element.value = '';
                }
            });
            
            // مسح قيمة البحث أيضاً
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.value = '';
            }
            
            // إعادة توجيه إلى الصفحة بدون فلاتر
            window.location.href = window.location.pathname;
        });
    }
    
    // دالة لتهيئة تأثيرات hover
    function initHoverEffects() {
        const hoverCards = document.querySelectorAll('.hover-card');
        hoverCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.classList.add('shadow-lg');
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.classList.remove('shadow-lg');
                this.style.transform = 'translateY(0)';
            });
        });
    }
    
    // تهيئة تأثيرات hover عند تحميل الصفحة
    initHoverEffects();
});

// تأثيرات hover للبطاقات
$(document).ready(function() {
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
    margin-bottom: 0.5rem;
}



.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.border-top {
    border-top: 1px solid #dee2e6 !important;
}

.fs-6 {
    font-size: 1rem !important;
}

.form-control-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.alert-info {
    border-right: 4px solid #0dcaf0;
}

.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    color: #007bff;
    border: 1px solid #dee2e6;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // جعل القوائم المنسدلة تحدث الصفحة فوراً
    const dropdowns = document.querySelectorAll('#sort, #per_page, select[name^="fields["][name$="]"]');
    
    dropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
    
    // جعل قوائم التاريخ المنسدلة تحدث الصفحة فوراً
    const dateDropdowns = document.querySelectorAll('select[name*="[day]"], select[name*="[month]"], select[name*="[year]"]');
    
    dateDropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
});
</script>

<style>
.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}
</style>
@endpush



@push('scripts')
<script>
// نظام الفلاتر بدون AJAX - تحديث الصفحة مباشرة
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filterForm');
    if (!form) return;
    
    // مستمع للتغييرات على القوائم المنسدلة - إرسال النموذج فوراً
    form.addEventListener('change', function(e) {
        const target = e.target;
        
        // التحقق من أن العنصر المتغير هو قائمة منسدلة للحقول المخصصة أو عناصر أخرى
        if (target.tagName === 'SELECT') {
            // إرسال النموذج مباشرة عند تغيير أي قائمة منسدلة
            form.submit();
        }
    });
    
    // زر مسح الفلاتر
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            // مسح جميع قيم النموذج
            Array.from(form.elements).forEach(element => {
                if (element.name && element.name.startsWith('fields[')) {
                    element.value = '';
                }
            });
            
            // مسح قيمة البحث أيضاً
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.value = '';
            }
            
            // إعادة توجيه إلى الصفحة بدون فلاتر
            window.location.href = window.location.pathname;
        });
    }
    
    // دالة لتهيئة تأثيرات hover
    function initHoverEffects() {
        const hoverCards = document.querySelectorAll('.hover-card');
        hoverCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.classList.add('shadow-lg');
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.classList.remove('shadow-lg');
                this.style.transform = 'translateY(0)';
            });
        });
    }
    
    // تهيئة تأثيرات hover عند تحميل الصفحة
    initHoverEffects();
});

// تأثيرات hover للبطاقات
$(document).ready(function() {
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
    margin-bottom: 0.5rem;
}



.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.border-top {
    border-top: 1px solid #dee2e6 !important;
}

.fs-6 {
    font-size: 1rem !important;
}

.form-control-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.alert-info {
    border-right: 4px solid #0dcaf0;
}

.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    color: #007bff;
    border: 1px solid #dee2e6;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // جعل القوائم المنسدلة تحدث الصفحة فوراً
    const dropdowns = document.querySelectorAll('#sort, #per_page, select[name^="fields["][name$="]"]');
    
    dropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
    
    // جعل قوائم التاريخ المنسدلة تحدث الصفحة فوراً
    const dateDropdowns = document.querySelectorAll('select[name*="[day]"], select[name*="[month]"], select[name*="[year]"]');
    
    dateDropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
});
</script>

<style>
.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}
</style>
@endpush



@push('scripts')
<script>
// نظام الفلاتر بدون AJAX - تحديث الصفحة مباشرة
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filterForm');
    if (!form) return;
    
    // مستمع للتغييرات على القوائم المنسدلة - إرسال النموذج فوراً
    form.addEventListener('change', function(e) {
        const target = e.target;
        
        // التحقق من أن العنصر المتغير هو قائمة منسدلة للحقول المخصصة أو عناصر أخرى
        if (target.tagName === 'SELECT') {
            // إرسال النموذج مباشرة عند تغيير أي قائمة منسدلة
            form.submit();
        }
    });
    
    // زر مسح الفلاتر
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            // مسح جميع قيم النموذج
            Array.from(form.elements).forEach(element => {
                if (element.name && element.name.startsWith('fields[')) {
                    element.value = '';
                }
            });
            
            // مسح قيمة البحث أيضاً
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.value = '';
            }
            
            // إعادة توجيه إلى الصفحة بدون فلاتر
            window.location.href = window.location.pathname;
        });
    }
    
    // دالة لتهيئة تأثيرات hover
    function initHoverEffects() {
        const hoverCards = document.querySelectorAll('.hover-card');
        hoverCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.classList.add('shadow-lg');
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.classList.remove('shadow-lg');
                this.style.transform = 'translateY(0)';
            });
        });
    }
    
    // تهيئة تأثيرات hover عند تحميل الصفحة
    initHoverEffects();
});

// تأثيرات hover للبطاقات
$(document).ready(function() {
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
    margin-bottom: 0.5rem;
}



.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.border-top {
    border-top: 1px solid #dee2e6 !important;
}

.fs-6 {
    font-size: 1rem !important;
}

.form-control-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.alert-info {
    border-right: 4px solid #0dcaf0;
}

.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    color: #007bff;
    border: 1px solid #dee2e6;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // جعل القوائم المنسدلة تحدث الصفحة فوراً
    const dropdowns = document.querySelectorAll('#sort, #per_page, select[name^="fields["][name$="]"]');
    
    dropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
    
    // جعل قوائم التاريخ المنسدلة تحدث الصفحة فوراً
    const dateDropdowns = document.querySelectorAll('select[name*="[day]"], select[name*="[month]"], select[name*="[year]"]');
    
    dateDropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
});
</script>

<style>
.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}
</style>
@endpush
@endsection