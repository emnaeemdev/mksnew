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
        $searchText = $this->searchService->buildSearchText($document);
        if ($document->search_text !== $searchText) {
            $document->search_text = $searchText;
            $document->saveQuietly();
        }
    }
}
