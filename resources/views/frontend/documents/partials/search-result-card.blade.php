<div class="col-lg-4 col-md-6">
    @php
        $locale = app()->getLocale();
        $sectionSlug = optional($document->section)->slug;
        $documentShowUrl = $sectionSlug
            ? route('content.show', [$locale, $sectionSlug, $document->id])
            : route('content.show', [$locale, 'documents', $document->id]);
        $searchHelper = app(\App\Services\DocumentSearchService::class);
        $searchParam = trim((string) request('search', $rawSearch ?? ''));
        if ($searchParam !== '') {
            $documentShowUrl .= (str_contains($documentShowUrl, '?') ? '&' : '?') . 'search=' . urlencode($searchParam);
        }
    @endphp
    <a href="{{ $documentShowUrl }}" 
       class="text-decoration-none">
        <div class="card h-100 doc-card document-card">
            <div class="card-body p-3">
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
                                {{-- <span class="badge bg-success me-2">
                                    <i class="fas fa-quote-left me-1"></i>
                                    مطابقة تامة
                                </span> --}}
                            @elseif($matchType === 'all')
                                {{-- <span class="badge bg-primary me-2">
                                    <i class="fas fa-check-double me-1"></i>
                                    جميع الكلمات
                                </span> --}}
                            @elseif($matchType === 'any')
                                {{-- <span class="badge bg-info me-2"> --}}
                                     {{-- الكلمات الموجودة في الوثيقة من البحث --}}
                                    {{-- <i class="fas fa-check me-1"></i> --}}
                                    @if(request('search'))
                                        @php
                                            $searchWords = explode(' ', trim(request('search')));
                                            $matchedWords = [];
                                            foreach($searchWords as $searchWord) {
                                                if(stripos($document->title, $searchWord) !== false || 
                                                   stripos($document->content, $searchWord) !== false || 
                                                   stripos($document->excerpt, $searchWord) !== false) {
                                                    $matchedWords[] = $searchWord;
                                                }
                                            }
                                        @endphp
                                        @if(count($matchedWords) > 0)
                                            {{-- <small class="d-block mt-1"> --}}
                                                {{-- ({{ implode(', ', $matchedWords) }}) --}}
                                            {{-- </small> --}}
                                        @endif
                                    @endif
                                {{-- </span> --}}
                            @endif
                        </div>
                        
                        <!-- العنوان -->
                        <h5 class="card-title mb-2">
                            {{ $document->title }}
                        </h5>
                        
                        <!-- المقتطف -->
                        @if($document->excerpt)
                            @php
                                $excerptPlain = strip_tags($document->excerpt);
                                $excerptShort = Str::limit($excerptPlain, 150);
                            @endphp
                            <p class="text-muted mb-3">
                                @if($matchType === 'exact' && !empty($rawSearch))
                                    @php
                                        $excerptMarked = $searchHelper->highlightTokenInText($excerptShort, $searchHelper->normalizeArabic($rawSearch), true);
                                    @endphp
                                    {!! $excerptMarked !!}
                                @elseif($matchType === 'all')
                                    @php
                                        $words = isset($tokens) && is_array($tokens) ? $tokens : preg_split('/\s+/u', trim(request('search', '')), -1, PREG_SPLIT_NO_EMPTY);
                                        $excerptMarked = $excerptShort;
                                        foreach (array_unique(array_filter($words)) as $w) {
                                            $excerptMarked = $searchHelper->highlightTokenInText($excerptMarked, $searchHelper->normalizeArabic($w), true);
                                        }
                                    @endphp
                                    {!! $excerptMarked !!}
                                @elseif($matchType === 'any' && (!empty($tokens) || !empty($word)))
                                    @php
                                        $highlightWords = !empty($tokens) && is_array($tokens)
                                            ? array_unique(array_filter($tokens))
                                            : array_filter([$word ?? '']);
                                        $excerptMarked = $excerptShort;
                                        foreach ($highlightWords as $w) {
                                            $excerptMarked = $searchHelper->highlightTokenInText($excerptMarked, $searchHelper->normalizeArabic($w), true);
                                        }
                                    @endphp
                                    {!! $excerptMarked !!}
                                @else
                                    {{ $excerptShort }}
                                @endif
                            </p>
                        @endif
                        
                        <!-- معلومات إضافية -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <div class="text-muted small">
                                @if($document->plainFieldValues->count() > 0)
                                    <div class="mb-3">
                                        @foreach($document->plainFieldValues->take(5) as $fieldValue)
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
                            @php
                                $sourceText = $document->content ?: ($document->excerpt ?: $document->title);
                                $sourceText = is_string($sourceText) ? $sourceText : '';
                                $clean = strip_tags($sourceText ?? '');
                                $clean = html_entity_decode($clean, ENT_QUOTES, 'UTF-8');
                            @endphp

                            <div class="mt-2 border-top pt-2">
                                <div class="small text-muted" style="min-height: 80px; max-height: 140px; overflow: hidden; direction: rtl;">
                                    @php
                                        $rawSearchText = trim((string) ($rawSearch ?? request('search', '')));
                                        $snippetTokens = !empty($tokens) && is_array($tokens)
                                            ? array_values(array_unique(array_filter($tokens)))
                                            : array_values(array_filter([$word ?? '']));
                                        $highlightWords = $rawSearchText !== ''
                                            ? $searchHelper->highlightTokensForSearch($rawSearchText, $matchType, $snippetTokens)
                                            : $snippetTokens;
                                        $snippets = $rawSearchText !== ''
                                            ? $searchHelper->findDocumentSearchSnippets($document, $rawSearchText, $matchType, $snippetTokens)
                                            : [];
                                    @endphp
                                    @forelse($snippets as $sn)
                                        <div>{!! $searchHelper->renderSnippetHtml($sn, $highlightWords) !!}</div>
                                    @empty
                                        <div class="text-muted">لم يُعثر على مقطع مطابق في النص المعروض.</div>
                                    @endforelse
                                </div>
                            </div>
                    </div>
                </div>
            </div>
            </div> <!-- closes .card -->
         </a>
     </div> <!-- closes .col-lg-4 .col-md-6 -->
