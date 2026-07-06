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
        $index = $this->searchService->buildSearchIndex($document);
        if ($document->search_text !== $index['search_text'] || $document->search_words !== $index['search_words']) {
            $document->search_text = $index['search_text'];
            $document->search_words = $index['search_words'];
            $document->saveQuietly();
        }
    }
}
