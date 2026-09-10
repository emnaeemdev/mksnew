<?php

namespace App\Observers;

use App\Models\Document;
use App\Services\DocumentSearchService;

class DocumentObserver
{
    public function __construct(protected DocumentSearchService $searchService)
    {
    }

    public function saved(Document $document): void
    {
        $document->loadMissing('plainFieldValues.field');
        $index = $this->searchService->buildSearchIndex($document);
        $indexChanged = $document->search_text !== $index['search_text']
            || $document->search_words !== $index['search_words'];

        if ($indexChanged) {
            $document->search_text = $index['search_text'];
            $document->search_words = $index['search_words'];
            $document->saveQuietly();
            $this->searchService->syncSearchTokensForDocument((int) $document->id, $index['search_words']);
        } elseif ($document->wasRecentlyCreated) {
            // حماية للوثيقة الجديدة حتى لو صادف أن search_text كان مجهزًا قبل الحفظ.
            $this->searchService->syncSearchTokensForDocument((int) $document->id, $index['search_words']);
        }

        if ($indexChanged || $document->wasRecentlyCreated || $this->searchVisibilityOrOrderChanged($document)) {
            $this->searchService->bumpSearchCacheVersion();
        }
    }

    public function deleted(Document $document): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('document_search_tokens')) {
            \Illuminate\Support\Facades\DB::table('document_search_tokens')
                ->where('document_id', $document->id)
                ->delete();
        }

        $this->searchService->bumpSearchCacheVersion();
    }

    protected function searchVisibilityOrOrderChanged(Document $document): bool
    {
        foreach ([
            'section_id',
            'is_published',
            'published_at',
            'sort_order',
            'title',
        ] as $field) {
            if ($document->wasChanged($field)) {
                return true;
            }
        }

        return false;
    }
}
