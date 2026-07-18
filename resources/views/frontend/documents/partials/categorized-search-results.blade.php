@php
    $raw = $categorizedResults['raw'] ?? '';
    $rankedGroups = $categorizedResults['ranked_groups'] ?? [];
    $phraseGroup = collect($rankedGroups)->first(fn (array $group) => ($group['key'] ?? '') === 'phrase');
    $phraseTotal = (int) ($phraseGroup['count'] ?? (($categorizedResults['phrase'] ?? null) ? $categorizedResults['phrase']->total() : 0));
    $searchWordCount = count($categorizedResults['tokens'] ?? []);
    $summaryGroups = collect($rankedGroups)->filter(function (array $group) use ($searchWordCount) {
        $key = (string) ($group['key'] ?? '');
        $label = (string) ($group['label'] ?? '');

        if ($key === 'all' || $label === 'وثائق بها كل كلمات البحث' || $label === 'مطابقة تامة' || $key === 'phrase') {
            return false;
        }

        // كلمتان فقط: لا تظهر tags الكلمات المنفردة
        if ($searchWordCount <= 2) {
            return false;
        }

        return true;
    })->values()->all();
    $pageParamKeys = ['page', 'page_ranked', 'page_p', 'page_a', 'page_any', 'page_phrase', 'page_all', 'page_w0', 'page_w1', 'page_w2', 'page_w3', 'page_w4', 'match_group'];
    $baseQuery = request()->except($pageParamKeys);
    $selectedGroup = (string) ($categorizedResults['selected_match_group'] ?? request('match_group', ''));
    $rankedTotal = ($categorizedResults['ranked'] ?? null) ? (int) $categorizedResults['ranked']->total() : (int) ($categorizedResults['unique_total'] ?? 0);
@endphp

<div id="categorized-search-app" data-ajax-search="1" class="ranked-search-app">
    <div class="ranked-search-shell mt-3">
        <div class="ranked-search-header">
            <div>
                <h4 class="ranked-search-title">نتائج البحث</h4>
                <p class="ranked-search-query mb-0">{{ $raw }}</p>
            </div>
            <div class="ranked-search-total" data-ranked-search-total>{{ $rankedTotal }} وثيقة</div>
        </div>

        <div class="ranked-search-summary">
            <a href="{{ request()->url() . '?' . http_build_query(array_merge($baseQuery, ['search' => request('search'), 'sort' => request('sort'), 'per_page' => request('per_page'), 'section_select' => request('section_select'), 'fields' => request('fields'), 'match_group' => 'phrase'])) }}"
               class="ranked-summary-pill {{ $selectedGroup === 'phrase' ? 'active' : '' }}">
                مطابقة تامة <strong>{{ $phraseTotal }}</strong>
            </a>
            @foreach($summaryGroups as $group)
                <a href="{{ request()->url() . '?' . http_build_query(array_merge($baseQuery, ['search' => request('search'), 'sort' => request('sort'), 'per_page' => request('per_page'), 'section_select' => request('section_select'), 'fields' => request('fields'), 'match_group' => $group['key']])) }}"
                   class="ranked-summary-pill {{ !empty($group['active']) ? 'active' : '' }}">
                    {{ $group['label'] }} <strong>{{ $group['count'] }}</strong>
                </a>
            @endforeach
            @if($selectedGroup !== '')
                <a href="{{ request()->url() . '?' . http_build_query(array_merge($baseQuery, ['search' => request('search'), 'sort' => request('sort'), 'per_page' => request('per_page'), 'section_select' => request('section_select'), 'fields' => request('fields')])) }}"
                   class="ranked-summary-pill ranked-summary-clear">
                    كل النتائج
                </a>
            @endif
        </div>

        <div id="search-tab-content-wrapper">
            @include('frontend.documents.partials.categorized-search-tab-content', [
                'categorizedResults' => $categorizedResults,
                'baseQuery' => $baseQuery,
                'raw' => $raw,
            ])
        </div>
    </div>
</div>

@once
    <style>
        .ranked-search-app,
        .ranked-search-shell,
        .ranked-results-content,
        .ranked-results-list{max-width:100%;min-width:0;overflow-x:hidden}
        .ranked-search-shell{background:#fff;border:1px solid #e8edf3;border-radius:18px;padding:22px;max-width:100%;min-width:0;overflow:hidden}
        .ranked-search-header{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:18px}
        .ranked-search-title{margin:0 0 6px;color:#2b4596;font-weight:700}
        .ranked-search-query{color:#6c757d;font-weight:600}
        .ranked-search-total{display:inline-flex;align-items:center;padding:8px 14px;border-radius:999px;background:#2b4596;color:#fff;font-weight:700}
        .ranked-search-summary{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:22px}
        .ranked-summary-pill{display:inline-flex;align-items:center;gap:6px;padding:3px 9px;border-radius:996px;background:#eef3f7;color:#2b4596;text-decoration:none;border:1px solid transparent;font-size:14px}
        .ranked-summary-pill:hover{background:#e4ebf3;color:#1f3b87;text-decoration:none}
        .ranked-summary-pill.active{background:#dfe8fb;border-color:#c6d7f4}
        .ranked-summary-pill strong{font-size:.92rem}
        .ranked-summary-clear{background:#fff5e8;color:#b35b00}
        .ranked-results-list{display:grid;gap:16px}
        .ranked-result-link,.ranked-result-link:hover,.ranked-result-link:focus,.ranked-result-link:active{display:block;max-width:100%;min-width:0;color:inherit;text-decoration:none}
        .ranked-result-card{background:#fff;border:1px solid #e9640a;border-radius:18px;padding:20px;transition:border-color .2s ease,transform .2s ease;overflow:hidden;max-width:100%;min-width:0}
        .ranked-result-link:hover .ranked-result-card{border-color:#e9640a;transform:translateY(-2px)}
        .ranked-result-top{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:12px}
        .ranked-result-tags{display:flex;flex-wrap:wrap;gap:8px;min-width:0}
        .ranked-result-section,.ranked-result-label{display:inline-flex;align-items:center;padding:6px 11px;border-radius:999px;font-size:.82rem;font-weight:700;max-width:100%}
        .ranked-result-section{background:#2b4596;color:#fff}
        .ranked-result-label{background:#eef3f7;color:#2b4596}
        .ranked-result-views{color:#6c757d;font-size:.92rem;font-weight:600;flex-shrink:0}
        .ranked-result-title{color:#243f8f;font-weight:700;line-height:1.8;margin-bottom:10px;overflow-wrap:anywhere;word-break:break-word}
        .ranked-result-meta{display:flex;flex-wrap:wrap;gap:8px;color:#6c757d;font-size:.92rem;margin-bottom:12px;overflow-wrap:anywhere;word-break:break-word}
        .ranked-result-snippets{margin:0 0 14px;padding:12px 14px;background:#fbfcfe;border:1px solid #e8edf3;border-radius:12px}
        .ranked-result-snippet-line{color:#333;line-height:2;font-size:.95rem;overflow-wrap:anywhere;word-break:break-word;white-space:normal}
        .ranked-result-snippet-line + .ranked-result-snippet-line{margin-top:10px;padding-top:10px;border-top:1px dashed #e8edf3}
        .ranked-result-footer{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:14px;padding-top:12px;border-top:1px solid #f1f3f5;color:#6c757d;font-size:.9rem}
        .ranked-result-open{color:#2b4596;font-weight:700;order:1}
        .ranked-result-footer-tags{display:flex;flex-wrap:wrap;gap:8px;order:2}
        .ranked-result-footer-tag{display:inline-flex;align-items:center;gap:6px;padding:7px 12px;border-radius:5px;background:#f6f8fb;border:1px solid #2b4596;color:#4f5d73;font-size:.84rem;line-height:1.4}
        .ranked-result-footer-tag strong{color:#2b4596;font-weight:700}
        .ranked-result-card mark{background:#fff3b0;color:#6b4d00;padding:0 .15rem;border-radius:3px}
        @media (max-width: 768px) {
            .ranked-result-footer{align-items:flex-start}
            .ranked-result-open{width:100%}
            .ranked-result-footer-tags{width:100%}
        }
    </style>
    @push('scripts')
        <script src="{{ asset('js/document-search-ajax.js') }}?v=7"></script>
    @endpush
@endonce
