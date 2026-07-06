<?php

namespace App\Observers;

use App\Models\Document;
use App\Models\DocumentFieldValue;
use App\Services\DocumentSearchService;

class DocumentFieldValueObserver
{
    public function __construct(protected DocumentSearchService $searchService)
    {
    }

    public function saved(DocumentFieldValue $fieldValue): void
    {
        $this->rebuildParent($fieldValue);
    }

    public function deleted(DocumentFieldValue $fieldValue): void
    {
        $this->rebuildParent($fieldValue);
    }

    protected function rebuildParent(DocumentFieldValue $fieldValue): void
    {
        $document = Document::with('plainFieldValues')->find($fieldValue->document_id);
        if ($document) {
            $this->searchService->rebuildSearchText($document);
        }
    }
}
