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

        if ($document->search_text === $index['search_text'] && $document->search_words === $index['search_words']) {
            return;
        }

        $document->search_text = $index['search_text'];
        $document->search_words = $index['search_words'];
        $document->saveQuietly();
        $this->searchService->syncSearchTokensForDocument((int) $document->id, $index['search_words']);
    }

    public function deleted(Document $document): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('document_search_tokens')) {
            \Illuminate\Support\Facades\DB::table('document_search_tokens')
                ->where('document_id', $document->id)
                ->delete();
        }
    }
}
