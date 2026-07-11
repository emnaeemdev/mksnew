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
    @push('styles')
        <style>
            .ranked-search-shell{background:#fff;border:1px solid #e8edf3;border-radius:18px;padding:22px;max-width:100%;min-width:0;overflow:hidden}
            .ranked-search-header{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:18px}
            .ranked-search-title{margin:0 0 6px;color:#2b4596;font-weight:700}
            .ranked-search-query{color:#6c757d;font-weight:600}
            .ranked-search-total{display:inline-flex;align-items:center;padding:8px 14px;border-radius:999px;background:#2b4596;color:#fff;font-weight:700}
            .ranked-search-summary{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:22px}
            .ranked-summary-pill{display:inline-flex;align-items:center;gap:8px;padding:9px 14px;border-radius:999px;background:#eef3f7;color:#2b4596;font-weight:600;text-decoration:none;border:1px solid transparent}
            .ranked-summary-pill:hover{background:#e4ebf3;color:#1f3b87;text-decoration:none}
            .ranked-summary-pill.active{background:#dfe8fb;border-color:#c6d7f4}
            .ranked-summary-pill strong{font-size:.92rem}
            .ranked-summary-clear{background:#fff5e8;color:#b35b00}
        </style>
    @endpush
    @push('scripts')
        <script src="{{ asset('js/document-search-ajax.js') }}?v=6"></script>
    @endpush
@endonce
