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

@once
    @push('styles')
        <style>
            .ranked-search-app,
            .ranked-search-shell,
            .ranked-results-content,
            .ranked-results-list{max-width:100%;min-width:0;overflow-x:hidden}
            .ranked-results-list{display:grid;gap:16px}
            .ranked-result-link,
            .ranked-result-link:hover,
            .ranked-result-link:focus,
            .ranked-result-link:active{display:block;max-width:100%;min-width:0;color:inherit;text-decoration:none}
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
            .ranked-result-excerpt{color:#000000;line-height:2;margin-bottom:14px;overflow-wrap:anywhere;word-break:break-word;white-space:normal}
            .ranked-result-snippets{border-top:1px solid #f1f3f5;padding-top:12px;overflow:hidden}
            .ranked-result-snippet-line{color:#6c757d;line-height:2;font-size:.93rem;overflow-wrap:anywhere;word-break:break-word;white-space:normal}
            .ranked-result-snippet-line + .ranked-result-snippet-line{margin-top:8px}
            .ranked-result-footer{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:14px;padding-top:12px;border-top:1px solid #f1f3f5;color:#6c757d;font-size:.9rem}
            .ranked-result-open{color:#2b4596;font-weight:700;order:1}
            .ranked-result-footer-tags{display:flex;flex-wrap:wrap;gap:8px;order:2}
            
            .ranked-result-footer-tag{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:7px 12px;
    border-radius:5px;
    background:#f6f8fb;
    border:1px solid #2b4596;
    color:#4f5d73;
    font-size:.84rem;
    line-height:1.4;
}

            .ranked-result-footer-tag strong{color:#2b4596;font-weight:700}
            .ranked-result-card mark{background:#fff3b0;color:#6b4d00;padding:0 .15rem;border-radius:3px}
            @media (max-width: 768px) {
                .ranked-result-footer{align-items:flex-start}
                .ranked-result-open{width:100%}
                .ranked-result-footer-tags{width:100%}
            }
        </style>
    @endpush
@endonce
