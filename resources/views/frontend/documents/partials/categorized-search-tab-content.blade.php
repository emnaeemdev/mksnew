@php
    $activeTab = $activeTab ?? ($categorizedResults['active_tab'] ?? request('tab', 'phrase'));
    $raw = $raw ?? ($categorizedResults['raw'] ?? '');
    $pageParamKeys = ['page', 'page_p', 'page_a', 'page_any', 'page_phrase', 'page_all', 'page_w0', 'page_w1', 'page_w2', 'page_w3'];
    $baseQuery = $baseQuery ?? request()->except($pageParamKeys);
    $perWordItems = $perWordItems ?? ($categorizedResults['per_word'] ?? []);
    $tokensCount = $tokensCount ?? count($perWordItems);
@endphp

<div class="tab-content" id="search-tab-content">
    <div id="tab-phrase" class="tab-pane fade row mt-2 {{ $activeTab === 'phrase' ? 'show active' : '' }}" role="tabpanel" aria-labelledby="tab-link-phrase">
        <div class="col-12">
            <h5 class="mb-3"><i class="fas fa-quote-left ms-1"></i> وثائق مطابقة تمامًا لجملة البحث</h5>
            @if($activeTab === 'phrase' && ($categorizedResults['phrase'] ?? null) && $categorizedResults['phrase']->count())
                <div class="row g-3 mb-3 search-results-grid">
                    @foreach($categorizedResults['phrase'] as $document)
                        @include('frontend.documents.partials.search-result-card', ['document' => $document, 'matchType' => 'exact', 'rawSearch' => $raw])
                    @endforeach
                </div>
                <div class="d-flex justify-content-center mb-4 search-tab-pagination">
                    {{ $categorizedResults['phrase']->appends(array_merge($baseQuery, ['tab' => 'phrase']))->links() }}
                </div>
            @elseif($activeTab === 'phrase')
                <div class="alert alert-light">لا توجد نتائج مطابقة تمامًا لعبارة "{{ $raw }}"</div>
            @endif
        </div>
    </div>

    <div id="tab-all" class="tab-pane fade row mt-2 {{ $activeTab === 'all' ? 'show active' : '' }}" role="tabpanel" aria-labelledby="tab-link-all">
        <div class="col-12">
            <h5 class="mb-3"><i class="fas fa-check-double ms-1"></i> وثائق بها جميع كلمات البحث</h5>
            @if($activeTab === 'all' && ($categorizedResults['all'] ?? null) && $categorizedResults['all']->count())
                <div class="row g-3 mb-3 search-results-grid">
                    @foreach($categorizedResults['all'] as $document)
                        @include('frontend.documents.partials.search-result-card', ['document' => $document, 'matchType' => 'all', 'tokens' => ($categorizedResults['tokens'] ?? [])])
                    @endforeach
                </div>
                <div class="d-flex justify-content-center mb-4 search-tab-pagination">
                    {{ $categorizedResults['all']->appends(array_merge($baseQuery, ['tab' => 'all']))->links() }}
                </div>
            @elseif($activeTab === 'all')
                <div class="alert alert-light">لا توجد نتائج تحتوي كل كلمات البحث.</div>
            @endif
        </div>
    </div>

    @if(!empty($perWordItems) && $tokensCount > 1)
        @foreach($perWordItems as $idx => $item)
            @php
                $word = $item['word'] ?? '';
                $page = $item['page'] ?? null;
            @endphp
            <div id="tab-word-{{ $idx }}" class="tab-pane fade row mt-2 {{ $activeTab === 'word-'.$idx ? 'show active' : '' }}" role="tabpanel" aria-labelledby="tab-link-word-{{ $idx }}">
                <div class="col-12">
                    <h5 class="mb-3"><i class="fas fa-check ms-1"></i> نتائج بها كلمة "{{ $word }}"</h5>
                    @if($activeTab === 'word-'.$idx && $page && $page->count())
                        <div class="row g-3 mb-3 search-results-grid">
                            @foreach($page as $document)
                                @include('frontend.documents.partials.search-result-card', ['document' => $document, 'matchType' => 'any', 'word' => $word])
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-center mb-4 search-tab-pagination">
                            {{ $page->appends(array_merge($baseQuery, ['tab' => 'word-'.$idx]))->links() }}
                        </div>
                    @elseif($activeTab === 'word-'.$idx)
                        <div class="alert alert-light">لا توجد نتائج تحتوي كلمة "{{ $word }}".</div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</div>
