@php
    $raw = $raw ?? ($categorizedResults['raw'] ?? '');
    $pageParamKeys = ['page', 'page_ranked', 'page_p', 'page_a', 'page_any', 'page_phrase', 'page_all', 'page_w0', 'page_w1', 'page_w2', 'page_w3', 'page_w4'];
    $baseQuery = $baseQuery ?? request()->except($pageParamKeys);
    $ranked = $categorizedResults['ranked'] ?? null;
@endphp

<div class="ranked-results-content" id="search-tab-content">
    @if($ranked && $ranked->count())
        <div class="ranked-results-list">
            @foreach($ranked as $entry)
                @include('frontend.documents.partials.ranked-search-result-card', [
                    'entry' => $entry,
                    'rawSearch' => $raw,
                ])
            @endforeach
        </div>
        <div class="d-flex justify-content-center mt-4 search-tab-pagination">
            {{ $ranked->appends($baseQuery)->links() }}
        </div>
    @else
        <div class="alert alert-light">لا توجد نتائج مطابقة لعبارة "{{ $raw }}".</div>
    @endif
</div>
