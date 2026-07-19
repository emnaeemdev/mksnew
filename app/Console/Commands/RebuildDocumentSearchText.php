<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Services\DocumentSearchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RebuildDocumentSearchText extends Command
{
    protected $signature = 'documents:rebuild-search-text {--chunk=100 : Number of documents per batch}';

    protected $description = 'Rebuild search_text, search_words, and document_search_tokens for all documents';

    public function handle(DocumentSearchService $searchService): int
    {
        $chunk = max(1, (int) $this->option('chunk'));
        $total = Document::count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        Document::query()
            ->with('plainFieldValues.field')
            ->orderBy('id')
            ->chunkById($chunk, function ($documents) use ($searchService, $bar) {
                foreach ($documents as $document) {
                    $searchService->rebuildDocumentIndex($document);
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();

        Cache::flush();
        $this->info('Document search index rebuild completed (search_text + search_words + tokens).');

        return self::SUCCESS;
    }
}
