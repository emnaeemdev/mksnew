@php
    $sectionKeywords = $sectionKeywords ?? collect();
    $activeSectionKeyword = $activeSectionKeyword ?? null;
    $baseUrl = route('frontend.documents.section', [app()->getLocale(), $section->slug]);
    // الإبقاء على خيارات العرض المشتركة مع الفلتر (عدد النتائج / الترتيب)
    $keywordListQuery = array_filter([
        'per_page' => request('per_page'),
        'sort' => request('sort'),
    ], fn ($value) => $value !== null && $value !== '');
@endphp

<nav class="section-keyword-strip mb-3" aria-label="مجموعات وثائق سريعة">
    <div class="section-keyword-strip__label">
        <i class="fas fa-layer-group"></i>
        <span> بحث سريع </span>
    </div>
    <div class="section-keyword-strip__scroll">
        <a href="{{ $keywordListQuery ? ($baseUrl . '?' . http_build_query($keywordListQuery)) : $baseUrl }}"
           class="section-keyword-chip {{ !$activeSectionKeyword ? 'is-active' : '' }}">
            كل الوثائق
            <span class="section-keyword-chip__count">{{ $totalDocuments ?? 0 }}</span>
        </a>

        @foreach($sectionKeywords as $kw)
            @php
                $label = $kw->pivot->label_override ?: $kw->name;
                $isActive = $activeSectionKeyword && $activeSectionKeyword->id === $kw->id;
                $kwQuery = array_merge($keywordListQuery, ['kw' => $kw->slug]);
            @endphp
            <a href="{{ $baseUrl . '?' . http_build_query($kwQuery) }}"
               class="section-keyword-chip {{ $isActive ? 'is-active' : '' }}"
               title="{{ $kw->name }}">
                {{ $label }}
                <span class="section-keyword-chip__count">{{ (int) ($kw->docs_count ?? $kw->section_docs_count ?? 0) }}</span>
            </a>
        @endforeach
    </div>
</nav>
