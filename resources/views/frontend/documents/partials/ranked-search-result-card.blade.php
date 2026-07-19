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
        ? route('content.show', [$locale, $sectionSlug, $document->id])
        : route('content.show', [$locale, 'documents', $document->id]);
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

    $footerFieldTargets = [
        'case_number' => ['رقم الحكم', 'رقم الطعن', 'رقم القضية'],
        'issue_date' => ['تاريخ الإصدار', 'تاريخ الحكم', 'تاريخ النطق'],
        'judicial_year' => ['السنة القضائية'],
        'judgment_category' => ['تصنيف الحكم'],
    ];

    $footerFieldValues = [
        'case_number' => null,
        'issue_date' => null,
        'judicial_year' => null,
        'judgment_category' => null,
    ];

    foreach (($document->plainFieldValues ?? collect()) as $fieldValue) {
        $fieldLabel = trim((string) ($fieldValue->field->label ?? ''));
        if ($fieldLabel === '' || $fieldValue->value === null || $fieldValue->value === '') {
            continue;
        }

        foreach ($footerFieldTargets as $key => $possibleLabels) {
            if ($footerFieldValues[$key] !== null) {
                continue;
            }

            foreach ($possibleLabels as $possibleLabel) {
                if (str_contains($fieldLabel, $possibleLabel)) {
                    $footerFieldValues[$key] = $fieldValue->field->type === 'date'
                        ? \Carbon\Carbon::parse($fieldValue->value)->format('Y-m-d')
                        : trim((string) $fieldValue->value);
                    break;
                }
            }
        }
    }

    $footerTags = array_filter([
        'رقم الحكم' => $footerFieldValues['case_number'],
        'تاريخ الإصدار' => $footerFieldValues['issue_date'],
        'السنة القضائية' => $footerFieldValues['judicial_year'],
        'تصنيف الحكم' => $footerFieldValues['judgment_category'],
    ], fn ($value) => $value !== null && $value !== '');
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
            @if(!empty($footerTags))
                <div class="ranked-result-footer-tags">
                    @foreach($footerTags as $tagLabel => $tagValue)
                        <span class="ranked-result-footer-tag">
                            <strong>{{ $tagLabel }}:</strong>
                            {{ $tagValue }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </article>
</a>
