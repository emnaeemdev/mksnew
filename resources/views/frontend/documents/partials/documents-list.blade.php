<!-- عرض إحصائيات النتائج عند استخدام الفلاتر فقط -->
@if(request('fields'))
    <div class="row mb-4 search-results">
        <div class="col-12">
            <div class="alert alert-info d-flex align-items-center">
                <i class="fas fa-info-circle me-2"></i>
                <div>
                    تم العثور على <strong>{{ $documents->total() }}</strong> وثيقة
                    مع الفلاتر المحددة
                </div>
            </div>
        </div>
    </div>
@endif

<!-- قائمة الوثائق -->
<div class="documents-container">
@if($documents->count() > 0)
    <div class="row g-4 mb-5">
        @foreach($documents as $document)
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                         style="height: 200px;">
                        <i class="fas fa-file-alt text-muted" style="font-size: 3rem;"></i>
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            @if($document->is_featured)
                                <span class="badge bg-warning text-dark me-1">مميز</span>
                            @endif
                            <span class="badge bg-primary">{{ $section->name }}</span>
                        </div>
                        
                        <h5 class="card-title">
                            <a href="{{ route('content.show', [app()->getLocale(), $section->name, $document->id]) }}" 
                               class="text-decoration-none text-dark">
                                {{ Str::limit($document->title, 60) }}
                            </a>
                        </h5>
                        
                        @if($document->excerpt)
                            <p class="card-text text-muted flex-grow-1">
                                {{ Str::limit($document->excerpt, 100) }}
                            </p>
                        @endif
                        
                        <!-- الحقول المخصصة المهمة -->
                        @if($document->fieldValues->count() > 0)
                            <div class="mb-3">
                                @foreach($document->fieldValues->take(2) as $fieldValue)
                                    @if($fieldValue->field->type == 'select' || $fieldValue->field->type == 'date')
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
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    {{ $document->published_at->format('Y-m-d') }}
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-eye me-1"></i>
                                    {{ number_format($document->views_count) }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
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