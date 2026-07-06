@php
    $raw = $categorizedResults['raw'] ?? '';
    $activeTab = $categorizedResults['active_tab'] ?? request('tab');
    $perWordItems = $categorizedResults['per_word'] ?? [];
    if (!$activeTab) {
        $activeTab = (($categorizedResults['phrase'] ?? null) && $categorizedResults['phrase']->total() > 0) ? 'phrase'
            : ((($categorizedResults['all'] ?? null) && $categorizedResults['all']->total() > 0) ? 'all' : 'phrase');
        foreach ($perWordItems as $wi => $item) {
            $page = is_array($item) ? ($item['page'] ?? null) : $item;
            if ($activeTab === 'phrase' && !($categorizedResults['phrase'] ?? null)?->total() && !($categorizedResults['all'] ?? null)?->total() && $page && $page->total() > 0) {
                $activeTab = 'word-' . $wi;
            }
        }
    }
    $pageParamKeys = ['page', 'page_p', 'page_a', 'page_any', 'page_phrase', 'page_all', 'page_w0', 'page_w1', 'page_w2', 'page_w3'];
    $baseQuery = request()->except($pageParamKeys);
    $phraseTotal = ($categorizedResults['phrase'] ?? null) ? $categorizedResults['phrase']->total() : 0;
    $allTotal = ($categorizedResults['all'] ?? null) ? $categorizedResults['all']->total() : 0;
    $tokensCount = count($perWordItems);
@endphp

<div id="categorized-search-app" data-ajax-search="1">
    <div class="row mt-3" id="results-tabs">
        <div class="col-12">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <button type="button"
                            id="tab-link-phrase"
                            class="nav-link {{ $activeTab === 'phrase' ? 'active' : '' }}"
                            data-search-tab="phrase"
                            aria-controls="tab-phrase">
                        مطابقة تامة
                        <span class="badge rounded-pill bg-secondary ms-1">{{ $phraseTotal }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button"
                            id="tab-link-all"
                            class="nav-link {{ $activeTab === 'all' ? 'active' : '' }}"
                            data-search-tab="all"
                            aria-controls="tab-all">
                        جميع الكلمات
                        <span class="badge rounded-pill bg-secondary ms-1">{{ $allTotal }}</span>
                    </button>
                </li>
                @if(!empty($perWordItems) && $tokensCount > 1)
                    @foreach($perWordItems as $idx => $item)
                        @php
                            $word = $item['word'] ?? '';
                            $page = $item['page'] ?? null;
                        @endphp
                        <li class="nav-item">
                            <button type="button"
                                    id="tab-link-word-{{ $idx }}"
                                    class="nav-link {{ $activeTab === 'word-'.$idx ? 'active' : '' }}"
                                    data-search-tab="word-{{ $idx }}"
                                    aria-controls="tab-word-{{ $idx }}">
                                نتائج بها كلمة <b style="color: rgb(124, 190, 86);">"{{ $word }}"</b>
                                <span class="badge rounded-pill bg-secondary ms-1">{{ $page ? $page->total() : 0 }}</span>
                            </button>
                        </li>
                    @endforeach
                @endif
            </ul>
        </div>
    </div>

    <div id="search-tab-content-wrapper">
        @include('frontend.documents.partials.categorized-search-tab-content', [
            'categorizedResults' => $categorizedResults,
            'activeTab' => $activeTab,
            'raw' => $raw,
            'baseQuery' => $baseQuery,
            'tokensCount' => $tokensCount,
            'perWordItems' => $perWordItems,
        ])
    </div>
</div>

@once
    @push('scripts')
        <script src="{{ asset('js/document-search-ajax.js') }}?v=2"></script>
    @endpush
@endonce
