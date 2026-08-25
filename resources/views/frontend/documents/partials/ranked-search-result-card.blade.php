@php
    $entry = $entry ?? [];
    $document = $entry['document'];
    $matchType = $entry['match_type'] ?? 'any';
    $tokens = $entry['tokens'] ?? [];
    $word = $entry['word'] ?? null;
    $label = $entry['label'] ?? '';
    $locale = app()->getLocale();
    $sectionSlug = optional($document->section)->slug;
    $documentShowUrl = $sectionSlug
        ? route('content.show', [$locale, $sectionSlug, $document->publicId()])
        : route('content.show', [$locale, 'documents', $document->publicId()]);
    $searchHelper = app(\App\Services\DocumentSearchService::class);
    $searchParam = trim((string) request('search', $rawSearch ?? ''));
    $rawSearchText = $searchParam !== '' ? $searchParam : trim((string) ($rawSearch ?? ''));

    if ($searchParam !== '') {
        $documentShowUrl .= (str_contains($documentShowUrl, '?') ? '&' : '?') . 'search=' . urlencode($searchParam);
    }

    $highlightWords = $rawSearchText !== ''
        ? $searchHelper->highlightTokensForSearch($rawSearchText, $matchType, is_array($tokens) ? $tokens : [])
        : (!empty($tokens) && is_array($tokens)
            ? array_values(array_unique(array_filter($tokens)))
            : array_filter([$word]));

    // المقتطف الأساسي: مقطع قبل/بعد كلمة البحث مع تظليل — وليس بداية الوثيقة
    $snippets = $rawSearchText !== ''
        ? $searchHelper->findDocumentSearchSnippets(
            $document,
            $rawSearchText,
            $matchType,
            is_array($tokens) ? $tokens : []
        )
        : [];

    $snippetHtml = '';
    foreach ($snippets as $sn) {
        $line = trim($searchHelper->renderSnippetHtml($sn, $highlightWords));
        if ($line !== '') {
            $snippetHtml .= '<div class="ranked-result-snippet-line">' . $line . '</div>';
        }
    }

    if ($snippetHtml === '') {
        $displaySource = $searchHelper->documentDisplaySource($document);
        $excerptShort = $searchHelper->plainTextForPreview($displaySource, 220, $highlightWords);
        if ($excerptShort !== '') {
            $snippetHtml = '<div class="ranked-result-snippet-line">'
                . $searchHelper->highlightSearchTokensInText(e($excerptShort), $highlightWords, true)
                . '</div>';
        }
    }

    $displayFieldValues = collect($document->plainFieldValues ?? [])
        ->filter(function ($fieldValue) {
            if (!$fieldValue->relationLoaded('field') || !$fieldValue->field) {
                return false;
            }

            if (method_exists($fieldValue->field, 'trashed') && $fieldValue->field->trashed()) {
                return false;
            }

            if ($fieldValue->value === null || trim((string) $fieldValue->value) === '') {
                return false;
            }

            return in_array($fieldValue->field->type, ['select', 'date', 'text'], true);
        })
        ->sortBy(fn ($fieldValue) => $fieldValue->field->sort_order ?? 999)
        ->take(5)
        ->values();
@endphp

<a href="{{ $documentShowUrl }}" class="text-decoration-none ranked-result-link">
    <article class="ranked-result-card">
        <div class="ranked-result-top">
            <div class="ranked-result-tags">
                <span class="ranked-result-section">{{ app()->getLocale() === 'ar' ? (optional($document->section)->name ?? 'غير مصنف') : (optional($document->section)->name_en ?? 'Uncategorized') }}</span>
                <span class="ranked-result-label">{{ $label }}</span>
            </div>
            <div class="ranked-result-views">
                <i class="fas fa-eye"></i>
                {{ number_format($document->views_count) }}
            </div>
        </div>

        <h4 class="ranked-result-title">{{ $searchHelper->plainTextForPreview($document->title, 300) }}</h4>

        <div class="ranked-result-meta">
            @if($document->published_at)
                <span>{{ \Carbon\Carbon::parse($document->published_at)->format('Y-m-d') }}</span>
            @endif
            @if(!empty($highlightWords))
                <span>·</span>
                <span>{{ implode(' + ', $highlightWords) }}</span>
            @endif
        </div>

        @if($snippetHtml !== '')
            <div class="ranked-result-snippets">
                {!! $snippetHtml !!}
            </div>
        @endif

        <div class="ranked-result-footer">
            <span class="ranked-result-open">عرض الوثيقة</span>
            @if($displayFieldValues->isNotEmpty())
                <div class="ranked-result-footer-tags">
                    @foreach($displayFieldValues as $fieldValue)
                        <span class="ranked-result-footer-tag">
                            <strong>{{ $fieldValue->field->label }}:</strong>
                            @if($fieldValue->field->type === 'date')
                                {{ \Carbon\Carbon::parse($fieldValue->value)->format('Y-m-d') }}
                            @else
                                {{ $fieldValue->value }}
                            @endif
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </article>
</a>
