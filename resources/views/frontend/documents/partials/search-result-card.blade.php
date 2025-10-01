<div class="col-12">
    @php
        $locale = app()->getLocale();
        $sectionSlug = optional($document->section)->slug;
        $documentShowUrl = $sectionSlug
            ? route('content.show', [$locale, $sectionSlug, $document->id])
            : route('content.show', [$locale, 'documents', $document->id]);
    @endphp
    <a href="{{ $documentShowUrl }}" 
       class="text-decoration-none">
        <div class="card border-0 shadow-sm h-100 document-card @if($matchType === 'exact') border-success @elseif($matchType === 'all') border-primary @elseif($matchType === 'any') border-info @endif">
            <div class="card-body p-4">
                <div class="row">
                    <!-- أيقونة الملف -->
                    
                    <!-- المحتوى -->
                    <div class="col-md-9 col-lg-10">
                        <!-- العلامات والحالة -->
                        <div class="mb-2">
                            <span class="badge bg-primary me-2">{{ app()->getLocale() === 'ar' ? (optional($document->section)->name ?? 'غير مصنّف') : (optional($document->section)->name_en ?? 'Uncategorized') }}</span>
                            @if($document->is_featured)
                                <span class="badge bg-warning text-dark me-2">مميز</span>
                            @endif
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-eye me-1"></i>
                                {{ number_format($document->views_count) }}
                            </span>
                            
                            <!-- نوع المطابقة -->
                            @if($matchType === 'exact')
                                <span class="badge bg-success me-2">
                                    <i class="fas fa-quote-left me-1"></i>
                                    مطابقة تامة
                                </span>
                            @elseif($matchType === 'all')
                                <span class="badge bg-primary me-2">
                                    <i class="fas fa-check-double me-1"></i>
                                    جميع الكلمات
                                </span>
                            @elseif($matchType === 'any')
                                <span class="badge bg-info me-2">
                                     الكلمات الموجودة في الوثيقة من البحث
                                    <i class="fas fa-check me-1"></i>
                                    @if(request('search'))
                                        @php
                                            $searchWords = explode(' ', trim(request('search')));
                                            $matchedWords = [];
                                            foreach($searchWords as $word) {
                                                if(stripos($document->title, $word) !== false || 
                                                   stripos($document->content, $word) !== false || 
                                                   stripos($document->excerpt, $word) !== false) {
                                                    $matchedWords[] = $word;
                                                }
                                            }
                                        @endphp
                                        @if(count($matchedWords) > 0)
                                            <small class="d-block mt-1">
                                                ({{ implode(', ', $matchedWords) }})
                                            </small>
                                        @endif
                                    @endif
                                </span>
                            @endif
                        </div>
                        
                        <!-- العنوان -->
                        <h5 class="card-title mb-2">
                            @if(request('search'))
                                {!! str_ireplace(request('search'), '<mark>' . request('search') . '</mark>', $document->title) !!}
                            @else
                                {{ $document->title }}
                            @endif
                        </h5>
                        
                        <!-- المقتطف -->
                        @if($document->excerpt)
                            <p class="text-muted mb-3">
                                @if(request('search'))
                                    {!! str_ireplace(request('search'), '<mark>' . request('search') . '</mark>', Str::limit($document->excerpt, 150)) !!}
                                @else
                                    {{ Str::limit($document->excerpt, 150) }}
                                @endif
                            </p>
                        @endif
                        
                        <!-- معلومات إضافية -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <div class="text-muted small">
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
                            </div>


                        </div>
                                                    <div>
                                <a href="{{ $documentShowUrl }}" class="text-dark">
                                    <i class="fas fa-eye me-1"></i> عرض
                                </a>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>
